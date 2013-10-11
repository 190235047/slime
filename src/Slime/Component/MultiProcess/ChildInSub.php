<?php
namespace Slime\Component\MultiProcess;

use Psr\Log\LoggerInterface;
use Slime\Component\Log;

/**
 *  构造函数:
 *     1. 以写方式打开子到父管道, 以读方式打开父到子管道
 *     2. 进入主Loop循环, 不断尝试读取主进程发来的消息
 *     3. 获取到消息后, 创建Job对象, 运行.
 *     4. 将结果(0:成功;else:失败)通过子到父的写管道写回主进程
 * Class ChildSub
 *
 * @package Slime\Component\MultiProcess
 */
class ChildInSub
{
    public function __construct($sPipeDir, ITask $Task, LoggerInterface $Log, $iMaxFinishCount)
    {
        # Init var
        $this->iPID = posix_getpid();
        if ($Log instanceof Log\Logger) {
            $Log->sGUID = "CHILD:$this->iPID";
        }
        $this->Log             = $Log;
        $this->Task            = $Task;
        $this->iMaxFinishCount = $iMaxFinishCount;

        $this->Log->debug('Child[{pid}] start', array('pid' => $this->iPID));

        $this->sFifoF2C = null;
        $this->sFifoC2F = null;
        PoolManager::generateFifo($this->iPID, $sPipeDir, $this->sFifoF2C, $this->sFifoC2F);

        # 等待父到子读管道创建成功 并只读打开
        $iRetry     = 0;
        $bFileExist = false;
        while ($iRetry++ < 30) {
            if (file_exists($this->sFifoF2C)) {
                $bFileExist = true;
                break;
            }
            usleep(100000);
        }
        if (!$bFileExist || ($m = fopen($this->sFifoF2C, 'r')) == false) {
            $Log->error('Fifo[{fifo}] open error', array('fifo' => $this->sFifoF2C));
            exit(1);
        }

        $this->rFifoF2C = $m;
        stream_set_blocking($this->rFifoF2C, 0);

        # 创建子到父写管道 并可写打开
        posix_mkfifo($this->sFifoC2F, 0600);
        $this->rFifoC2F = fopen($this->sFifoC2F, 'w');
        stream_set_blocking($this->rFifoC2F, 1);

        $this->iFinishCount = 0;

        # Log
        $this->Log->debug('Child[{pid}] ready', array('pid' => $this->iPID));

        # 进入Loop
        $this->receiveLoop();
    }

    /**
     * child main loop
     */
    private function receiveLoop()
    {
        while (true) {
            if (($sMessage = fgets($this->rFifoF2C)) === false) {
                //管道已经断掉, 退出
                if (!is_readable($this->sFifoF2C)) {
                    $this->Log->debug(
                        'Fifo[{fifo}] can not be read and process will exit',
                        array('fifo' => $this->sFifoF2C)
                    );
                    exit(1);
                }
                goto NEXT_LOOP;
            }
            $sMessage = substr($sMessage, 0, strlen($sMessage) - 1);

            # 忽略父进程检测
            if ($sMessage === '__NOOP__') {
                //$this->Log->debug('GET NOOP');
                goto NEXT_LOOP;
            }

            $iRS = 0;
            try {
                $this->Task->dealMsgInChild($sMessage, $this->Log);
            } catch (\Exception $E) {
                $iRS = 1;
            }

            if (fwrite($this->rFifoC2F, $iRS . "\n") === false) {
                //管道已经断掉, 退出
                $this->Log->debug(
                    'Fifo[{fifo}] can not be write and process will exit',
                    array('fifo' => $this->sFifoC2F)
                );
                exit(1);
            }

            if (++$this->iFinishCount >= $this->iMaxFinishCount) {
                $this->Log->debug(
                    'Child[{pid}] run {count} times and exit',
                    array('pid' => $this->iPID, 'count' => $this->iFinishCount)
                );
                exit(0);
            }

            unset($Job);

            NEXT_LOOP:
            usleep(100000);
        }
    }
}