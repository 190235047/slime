<?php
namespace SlimeFramework\Component\MultiProcess;

use SlimeFramework\Component\Log\Logger;

/**
 * In father process, this class has :
 *     1. method sendMessage(send to child process with block)
 *     2. method receiveMessage(receive from child process with none block)
 * In child process, this class will run in loop:
 *     1. block until receive the message from father process(sendMessage)
 *     2. create Job object with message and run
 *     3. send result to father process(receiveMessage) and go into next loop
 *
 * @package SlimeFramework\Component\MultiProcess
 *
 * @author:smallslime@gmail.com
 */
class Child
{
    public function __construct($sPipeDir, $sJobClass, Logger $Logger)
    {
        $this->sJobClass = $sJobClass;
        $this->Logger = $Logger;
        $sPipeDir = rtrim($sPipeDir, '/');

        $iPID = pcntl_fork();
        if ($iPID < 0) {
            trigger_error('fork error', E_USER_ERROR);
            exit(1);
        } elseif ($iPID) {
            # father
            $this->iPID = $iPID;

            $this->sFifoF2C = $sPipeDir . '/sf_mp_f2c_' . $this->iPID;
            $this->sFifoC2F = $sPipeDir . '/sf_mp_c2f_' . $this->iPID;
            posix_mkfifo($this->sFifoF2C, 0600);
            $this->rFifoF2C = fopen($this->sFifoF2C, 'w');
            stream_set_blocking($this->rFifoF2C, 1);
            $this->rFifoC2F = fopen($this->sFifoC2F, 'r');
            stream_set_blocking($this->rFifoC2F, 0);
        } else {
            # child
            $this->iPID = posix_getpid();
            $this->Logger->sGUID = "CHILD:$this->iPID";

            $this->Logger->debug('child start');

            $this->sFifoF2C = $sPipeDir . '/sf_mp_f2c_' . $this->iPID;
            $this->sFifoC2F = $sPipeDir . '/sf_mp_c2f_' . $this->iPID;
            posix_mkfifo($this->sFifoC2F, 0600);
            $this->rFifoF2C = fopen($this->sFifoF2C, 'r');
            stream_set_blocking($this->rFifoF2C, 1);
            $this->rFifoC2F = fopen($this->sFifoC2F, 'w');
            stream_set_blocking($this->rFifoC2F, 1);

            $this->Logger->debug('child ready');

            $this->_receive();
        }
    }

    /**
     * father send message
     *
     * @param $sMessage
     */
    public function sendMessage($sMessage)
    {
        fwrite($this->rFifoF2C, $sMessage . "\n");
    }

    /**
     * father receive message
     *
     * @return string
     */
    public function receiveMessage()
    {
        return fgets($this->rFifoC2F);
    }

    /**
     * child main loop
    */
    private function _receive()
    {
        while (true) {
            /** @var Task $Job */
            $Job = new $this->sJobClass(fgets($this->rFifoF2C), $this->Logger);
            $bResult = $Job->run();
            unset($Job);
            fwrite($this->rFifoC2F, ($bResult ? '0' : '1') . "\n");
        }
    }
}

