<?php
namespace Slime\Bundle\Framework\BGJob;

use Slime\Component\Log\LoggerInterface;

/**
 * Class Daemon
 *
 * @package Slime\Bundle\Framework\BGJob
 * @author  smallslime@gmail.com
 */
class Daemon
{
    /**
     * @param mixed           $mCBFetchMSG
     * @param mixed           $mCBDealMSG
     * @param LoggerInterface $MasterLog
     * @param LoggerInterface $ChildLog
     */
    public static function main($mCBFetchMSG, $mCBDealMSG, LoggerInterface $MasterLog, LoggerInterface $ChildLog)
    {
        while (true) {
            $MasterLog->info(
                sprintf(
                    "Start new loop[mem:%s memTop:%s]",
                    memory_get_usage(true),
                    memory_get_peak_usage(true)
                )
            );
            # get message
            $sMessage = call_user_func($mCBFetchMSG, $MasterLog);
            if ($sMessage === false) {
                goto NEXT;
            }
            $MasterLog->info('Main message[{message}] get in loop', array('message' => $sMessage));

            # do fork
            $iPID = pcntl_fork();
            if ($iPID < 0) {
                $MasterLog->critical('Fork error');
                goto NEXT;
            } elseif ($iPID) {
                $MasterLog->info('Fork child[{child}]', array('child' => $iPID));
                goto NEXT;
            } else {
                call_user_func($mCBDealMSG, $sMessage, clone($ChildLog));
                exit();
            }

            # next loop
            # wait
            NEXT:
            while (($iPID = pcntl_wait($iStatus, WNOHANG | WUNTRACED)) > 0) {
                $MasterLog->info("Get SIGCHLD from child[{child}]", array('child' => $iPID));
            }
        }
    }
}
