<?php
namespace Slime\Bundle\BGJob;

use Slime\Component\Log\LoggerInterface;

function main($mCBFetchMSG, $mCBDealMSG, LoggerInterface $MasterLog, LoggerInterface $ChildLog) {
    while (true) {
        do {
            $this->MasterLog->info(
                sprintf(
                    "Start new loop[mem:%s memTop:%s]",
                    memory_get_usage(true),
                    memory_get_peak_usage(true)
                )
            );
            # get message
            $sMessage = call_user_func($this->mCBFetchMSG, $this->MasterLog);
            if ($sMessage === false) {
                break;
            }
            $this->MasterLog->info('Main message[{message}] get in loop', array('message' => $sMessage));

            # do fork
            $iPID = pcntl_fork();
            if ($iPID < 0) {
                $this->MasterLog->critical('Fork error');
                break;
            } elseif ($iPID) {
                $this->MasterLog->info('Fork child[{child}]', array('child' => $iPID));
                break;
            } else {
                call_user_func($this->mCBDealMSG, $sMessage, clone($this->ChildLog));
                exit();
            }
        } while (0);

        # next loop
        # wait
        while (($iPID = pcntl_wait($iStatus, WNOHANG | WUNTRACED)) > 0) {
            $this->MasterLog->info("Get SIGCHLD from child[{child}]", array('child' => $iPID));
        }
    }
}

main();