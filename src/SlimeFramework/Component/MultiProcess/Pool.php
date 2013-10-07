<?php
namespace SlimeFramework\Component\MultiProcess;

use Psr\Log\LoggerInterface;

class Pool
{
    /** @var Child[] */
    private $aChild;
    /** @var Child[] */
    private $aIdleChild;
    /** @var Child[] */
    private $aBusyChild;

    const STATUS_IDLE = 1;
    const STATUS_BUSY = 2;

    public function __construct($iSize, $sPipeDir, $sJobClass, LoggerInterface $Log)
    {
        if (!file_exists($sPipeDir)) {
            mkdir($sPipeDir);
        }
        if (!is_writeable($sPipeDir)) {
            $Log->error('Fifo base dir[{fifo}] can not writeable', array('fifo' => $sPipeDir));
            exit(1);
        }

        for ($i = 0; $i < $iSize; $i++) {
            $Child                          = new Child($sPipeDir, $sJobClass, $Log);
            $this->aChild[$Child->iPID]     = $Child;
            $this->aIdleChild[$Child->iPID] = true;
        }
    }

    public function getOneIdleChild()
    {
        return count($this->aIdleChild) === 0 ? null : $this->aChild[array_rand($this->aIdleChild)];
    }

    public function getBusyChildren()
    {
        return $this->aBusyChild;
    }

    public function markChildIdle(Child $Child)
    {
        $this->changeChildStatus($Child, self::STATUS_IDLE);
    }

    public function markChildBusy(Child $Child)
    {
        $this->changeChildStatus($Child, self::STATUS_BUSY);
    }

    private function changeChildStatus(Child $Child, $iStatus)
    {
        if ($iStatus != self::STATUS_BUSY && $iStatus != self::STATUS_IDLE) {
            trigger_error('Param error', E_USER_ERROR);
            exit(1);
        }
        $iPID = $Child->iPID;
        if ($iStatus == self::STATUS_BUSY) {
            $this->aBusyChild[$iPID] = $Child;
            unset($this->aIdleChild[$iPID]);
        } else {
            $this->aIdleChild[$iPID] = $Child;
            unset($this->aBusyChild[$iPID]);
        }
    }
}