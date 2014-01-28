<?php
namespace Slime\Component\MultiProcessJob;

use Slime\Component\Log\Logger;

/**
 * Class Manager
 *
 * @package Slime\Component\MultiProcessJob
 * @author  smallslime@gmail.com
 */
class Manager
{
    public function __construct(
        $mCBFetchMSG,
        $mCBDealMSG,
        Logger $Log,
        $iMaxExecuteTime = 600
    ) {
        # var init
        $Log->sGUID = 'Main:' . posix_getpid() . '';
        $this->Log  = $Log;

        $this->mCBFetchMSG = $mCBFetchMSG;
        $this->mCBDealMSG  = $mCBDealMSG;

        # dy set
        $this->iMaxExecuteTime = $iMaxExecuteTime;
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
                        'mem'    => memory_get_usage(true),
                        'memTop' => memory_get_peak_usage(true),
                    )
                );
            }

            do {
                # get message
                $sMessage = call_user_func($this->mCBFetchMSG, $this->Log);
                if ($sMessage === false) {
                    break;
                }
                $this->Log->info('Main message[{message}] get in loop', array('message' => $sMessage));

                # do fork
                $iPID = pcntl_fork();
                if ($iPID < 0) {
                    $this->Log->critical('Fork error');
                    break;
                } elseif ($iPID) {
                    $this->Log->info('Fork child[{child}]', array('child' => $iPID));
                    break;
                } else {
                    # run in child
                    $iPID = posix_getpid();
                    $this->Log->info(
                        'Child[{child}] start with message[{msg}]',
                        array('child' => $iPID, 'msg' => $sMessage)
                    );
                    call_user_func($this->mCBDealMSG, $sMessage, $this->Log);
                    exit();
                }
            } while (0);

            # next loop
            # wait
            while (($iPID = pcntl_wait($iStatus, WNOHANG | WUNTRACED)) > 0) {
                $this->Log->info("Get SIGCHLD from child[{child}]", array('child' => $iPID));
            }

            # 如果此次 loop 不足1s, sleep to 1s . 不必十分精确
            if (($iInterval = 1 - (microtime(true) - $iTStart)) > 0) {
                usleep((int)($iInterval * 1000000));
            }
        }
    }
}
