<?php
namespace Slime\Component\MultiProcess;

class Child
{
    public $iWorkStartTimestamp = 0;
    public $sMessage;

    public function __construct($iPID, $sFifoF2C, $rFifoF2C, $sFifoC2F, $rFifoC2F)
    {
        $this->iPID     = $iPID;
        $this->sFifoF2C = $sFifoF2C;
        $this->rFifoF2C = $rFifoF2C;
        $this->sFifoC2F = $sFifoC2F;
        $this->rFifoC2F = $rFifoC2F;
    }

    /**
     * father send message
     *
     * @param string|null $sMessage
     * @return int|bool
     */
    public function send($sMessage = null)
    {
        return fwrite($this->rFifoF2C, ($sMessage===null ? $this->sMessage : $sMessage) . "\n");
    }

    /**
     * father receive message
     *
     * @return string|bool
     */
    public function receive()
    {
        return fgets($this->rFifoC2F);
    }
}