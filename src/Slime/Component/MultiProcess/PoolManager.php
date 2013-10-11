<?php
namespace Slime\Component\MultiProcess;

use Psr\Log\LoggerInterface;
use Slime\Component\Log;

class PoolManager
{
    /** @var Child[] */
    private $aChildren = array();
    /** @var Child[] */
    private $aIdleChildren = array();
    /** @var Child[] */
    private $aBusyChildren = array();

    private $iConfigFileLastModifiedTimestamp = 0;

    const STATUS_IDLE = 1;
    const STATUS_BUSY = 2;

    private $aCanBeMod = array(
        'iPoolSize',
        'iMaxFinishCount',
        'iMaxExecuteTime'
    );

    public function __construct(
        $sFifoDir,
        LoggerInterface $Log,
        ITask $Task,
        $sConfigFile = null,
        $iPoolSize = 20,
        $iMaxFinishCount = 100,
        $iMaxExecuteTime = 600
    ) {
        # var init
        if ($Log instanceof Log\Logger) {
            $Log->sGUID = 'Main:' . posix_getpid() . '';
        }

        $this->sFifoDir = $sFifoDir;
        $this->Log      = $Log;
        $this->Task     = $Task;

        # check fifo dir
        if (!file_exists($sFifoDir)) {
            mkdir($sFifoDir);
        }
        if (!is_writeable($sFifoDir)) {
            $Log->error('Fifo base dir[{fifo}] can not writeable', array('fifo' => $sFifoDir));
            exit(1);
        }

        # dy set
        $this->sConfigFile     = $sConfigFile;
        $this->iPoolSize       = $iPoolSize;
        $this->iMaxFinishCount = $iMaxFinishCount;
        $this->iMaxExecuteTime = $iMaxExecuteTime;

        # load overwrite
        $this->loadConfigFile();
    }

    public function run()
    {
        # create pool
        $this->createChildren($this->iPoolSize);

        $i = 0;
        while (true) {
            $iTStart = microtime(true);

            if (++$i == 1000000) {
                $i = 0;
            }
            if ($i % 3 === 0) {
                $this->Log->debug(
                    'Mem:{mem}, MemTop:{memTop}',
                    array(
                        'mem'      => memory_get_usage(true),
                        'memTop'   => memory_get_peak_usage(true),
                    )
                );
                $this->Log->debug('AllChildren:{c}',  array('c' => array_keys($this->aChildren)));
                $this->Log->debug('IdleChildren:{c}', array('c' => array_keys($this->aIdleChildren)));
                $this->Log->debug('BusyChildren:{c}', array('c' => array_keys($this->aBusyChildren)));
            }

            $Child = count($this->aIdleChildren) === 0 ? null : $this->aIdleChildren[array_rand($this->aIdleChildren)];
            if ($Child === null) {
                goto NEXT_LOOP;
            }

            # get message
            $sMessage = $this->Task->fetchMsgInMain($this->Log);
            if ($sMessage === false) {
                goto NEXT_LOOP;
            }
            $this->Log->debug('Main message[{message}] get in loop', array('message' => $sMessage));

            # send message to child
            $this->changeChildStatus($Child, self::STATUS_BUSY);
            $Child->iWorkStartTimestamp = time();
            $Child->sMessage = $sMessage;
            if ($Child->send()===false) {
                $this->Log->warning(
                    'Send message[{msg}] to child[{child}] failed',
                    array('msg' => $Child->sMessage, 'child' => $Child->iPID)
                );
                $this->cleanUpChild($Child);
            }

            # next loop
            NEXT_LOOP:
            foreach ($this->aChildren as $Child) {
                # 检查管道通信是否正常
                if (!is_readable($Child->sFifoC2F) || !is_writable($Child->sFifoF2C) || $Child->send('__NOOP__')===false) {
                    $this->Log->debug(
                        'Child[{child}] fifo broken',
                        array('child' => $Child->iPID)
                    );
                    $this->cleanUpChild($Child);
                }
                if (isset($this->aBusyChildren[$Child->iPID])) {
                    if (time() - $Child->iWorkStartTimestamp > $this->iMaxExecuteTime) {
                        # 超时
                        $this->Log->debug(
                            'Child[{child}] exec timeout and process will exit',
                            array('child' => $Child->iPID)
                        );
                        $this->cleanUpChild($Child);
                    } else {
                        # 消息
                        if (($mResult = $Child->receive()) !== false) {
                            if ((int)$mResult!==0) {
                                $this->Task->dealWhenException($Child->sMessage, $this->Log);
                            }
                            $this->changeChildStatus($Child, self::STATUS_IDLE);
                        }
                    }
                }
            }

            # wait
            while (($iPID = pcntl_wait($iStatus, WNOHANG | WUNTRACED)) > 0) {
                $this->Log->debug("Get SIGCHLD from child[{child}]", array('child' => $iPID));
                self::generateFifo($iPID, $this->sFifoDir, $sF2C, $sC2F);
                if (file_exists($sF2C)) {
                    $b = unlink($sF2C);
                    $this->Log->debug("Delete fifo[{fifo}] {st}", array('fifo' => $sF2C, 'st' => $b ? 'OK' : 'FAILED'));
                }
                if (file_exists($sC2F)) {
                    $b = unlink($sC2F);
                    $this->Log->debug("Delete fifo[{fifo}] {st}", array('fifo' => $sC2F, 'st' => $b ? 'OK' : 'FAILED'));
                }
            }

            # 创建新进程
            $iNeedCreate = $this->iPoolSize - count($this->aChildren);
            if ($iNeedCreate > 0) {
                $this->Log->debug("There are $iNeedCreate proecess need to create");
                $this->createChildren($iNeedCreate);
            }

            # 如果此次 loop 不足1s, sleep to 1s . 不必十分精确
            if (($iInterval = 1-(microtime(true) - $iTStart)) > 0) {
                usleep((int)($iInterval * 1000000));
            }
        }
    }

    private function createChildren($iSize)
    {
        if ($iSize > 0) {
            for ($i = 0; $i < $iSize; $i++) {
                $this->createChild();
            }
        }
    }

    private function createChild()
    {
        $iPID = pcntl_fork();
        if ($iPID < 0) {
            $this->Log->error('Fork error');
            exit(1);
        } elseif ($iPID) {
            $this->Log->debug('Fork child[{child}]', array('child' => $iPID));
            # init var
            self::generateFifo($iPID, $this->sFifoDir, $sFifoF2C, $sFifoC2F);

            # 创建可写 F-C 管道 并可写打开
            posix_mkfifo($sFifoF2C, 0600);
            $rFifoF2C = fopen($sFifoF2C, 'w');
            stream_set_blocking($rFifoF2C, 1);

            # 等待 C-F 管道创建 并只读打开
            $iRetry     = 0;
            $bFileExist = false;
            while ($iRetry++ < 30) {
                if (file_exists($sFifoC2F)) {
                    $bFileExist = true;
                    break;
                }
                usleep(100000);
            }
            if (!$bFileExist) {
                $this->Log->warning('Fifo[{fifo}] open error', array('fifo' => $sFifoC2F));
            }
            $rFifoC2F = fopen($sFifoC2F, 'r');
            stream_set_blocking($rFifoC2F, 0);

            # set in array
            $this->aChildren[$iPID] = $this->aIdleChildren[$iPID] = new Child($iPID, $sFifoF2C, $rFifoF2C, $sFifoC2F, $rFifoC2F);

            # Log
            $this->Log->debug('Child[{child}] create ok', array('child' => $iPID));
        } else {
            new ChildInSub($this->sFifoDir, $this->Task, $this->Log, $this->iMaxFinishCount);
        }
    }

    private function cleanUpChild(Child $Child)
    {
        $iPID = $Child->iPID;
        $b = posix_kill($iPID, SIGTERM);
        $this->Log->debug(
            'Send SIGTERM to child[{child}] : {status}',
            array('child' => $iPID, 'status' => $b ? 'OK' : 'Fail')
        );
        if (isset($this->aChildren[$iPID])) {
            unset($this->aChildren[$iPID]);
        }
        if (isset($this->aBusyChildren[$iPID])) {
            $this->Task->dealWhenException($this->aBusyChildren[$iPID]->sMessage, $this->Log);
            unset($this->aBusyChildren[$iPID]);
        } else {
            unset($this->aIdleChildren[$iPID]);
        }

        unset($Child);
    }

    private function changeChildStatus(Child $Child, $iStatus)
    {
        if ($iStatus != self::STATUS_BUSY && $iStatus != self::STATUS_IDLE) {
            $this->Log->error('param iStatus error');
            exit(1);
        }
        $Child->iWorkStartTimestamp = 0;
        $Child->sMessage = '';
        $iPID = $Child->iPID;
        if ($iStatus == self::STATUS_BUSY) {
            $this->aBusyChildren[$iPID] = $Child;
            unset($this->aIdleChildren[$iPID]);
        } else {
            $this->aIdleChildren[$iPID] = $Child;
            unset($this->aBusyChildren[$iPID]);
        }
    }

    private function loadConfigFile()
    {
        if ($this->sConfigFile !== null &&
            file_exists($this->sConfigFile) &&
            ($iTS = filemtime($this->sConfigFile)) !== $this->iConfigFileLastModifiedTimestamp
        ) {
            $this->iConfigFileLastModifiedTimestamp = $iTS;
            $aArr                                   = require $this->sConfigFile;
            foreach ($this->aCanBeMod as $sStr) {
                if (isset($aArr[$sStr])) {
                    $this->$sStr = $aArr[$sStr];
                }
            }
        }
    }

    public static function generateFifo($iPID, $sPipeDir, &$sFifoF2C, &$sFifoC2F)
    {
        $sFifoF2C = $sPipeDir . '/sf_mp_f2c_' . $iPID;
        $sFifoC2F = $sPipeDir . '/sf_mp_c2f_' . $iPID;
    }
}
