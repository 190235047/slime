<?php
namespace Slime\Component\MultiProcessJob;

use Psr\Log\LoggerInterface;
use Slime\Component\Log;

/**
 * Class Manager
 *
 * @package Slime\Component\MultiProcessJob
 * @author  smallslime@gmail.com
 */
class Manager
{
    /** @var Child[] */
    private $aChildren = array();

    private $iConfigFileLastModifiedTimestamp = 0;

    private $aCanBeMod = array(
        'iMaxExecuteTime'
    );

    public function __construct(
        LoggerInterface $Log,
        ITask $Task,
        $sConfigFile = null,
        $iMaxExecuteTime = 600
    ) {
        # var init
        if ($Log instanceof Log\Logger) {
            $Log->sGUID = 'Main:' . posix_getpid() . '';
        }

        $this->Log      = $Log;
        $this->Task     = $Task;

        # dy set
        $this->sConfigFile     = $sConfigFile;
        $this->iMaxExecuteTime = $iMaxExecuteTime;

        # load overwrite
        $this->loadConfigFile();
    }

    public function run()
    {
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
            }

            if (count($this->aChildren) >= $this->iMaxExecuteTime) {
                goto NEXT_LOOP;
            }

            # get message
            $sMessage = $this->Task->fetchMsgInMain($this->Log);
            if ($sMessage === false) {
                goto NEXT_LOOP;
            }
            $this->Log->debug('Main message[{message}] get in loop', array('message' => $sMessage));

            # do fork
            $iPID = pcntl_fork();
            if ($iPID < 0) {
                $this->Log->critical('Fork error');
            } elseif ($iPID) {
                $this->Log->debug('Fork child[{child}]', array('child' => $iPID));

                # set in array
                $this->aChildren[$iPID] = $iPID;
            } else {
                $iPID = posix_getpid();
                $this->Log->debug(
                    'Child[{child}] start with message[{msg}]',
                    array('child' => $iPID, 'msg' => $sMessage)
                );
                $this->Task->dealMsgInChild($sMessage, $this->Log);
                exit();
            }

            # next loop
            NEXT_LOOP:
            # wait
            while (($iPID = pcntl_wait($iStatus, WNOHANG | WUNTRACED)) > 0) {
                $this->Log->debug("Get SIGCHLD from child[{child}]", array('child' => $iPID));
                unset($this->aChildren[$iPID]);
            }

            # 如果此次 loop 不足1s, sleep to 1s . 不必十分精确
            if (($iInterval = 1-(microtime(true) - $iTStart)) > 0) {
                usleep((int)($iInterval * 1000000));
            }
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
}
