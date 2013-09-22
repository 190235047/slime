<?php
namespace SlimeFramework\Component\MultiProcess;

use SlimeFramework\Component\Log\Logger;

abstract class MPPoolModel
{
    public function __construct($iPoolSize, $sPipeDir, $sJobClass, $iLoopSleepUS = 1000, Logger $Logger)
    {
        $this->iPoolSize    = $iPoolSize;
        $this->sPipeDir     = $sPipeDir;
        $this->sJobClass    = $sJobClass;
        $Logger->sGUID      = 'MAIN';
        $this->iLoopSleepUS = $iLoopSleepUS;
        $this->Logger       = $Logger;
    }

    public function run()
    {
        $Pool = new Pool(
            $this->iPoolSize,
            $this->sPipeDir,
            $this->sJobClass,
            $this->Logger
        );
        while (true) {
            $sMessage = $this->getMessage();
            if ($sMessage === '') {
                //$this->Logger->debug('main loop next');
                goto NEXT_LOOP;
            }
            $this->Logger->debug('main loop get:' . $sMessage);

            $Child = $Pool->getOneIdleChild();
            if ($Child === null) {
                goto NEXT_LOOP;
            }
            $Pool->markChildBusy($Child);
            $Child->sendMessage($sMessage);

            NEXT_LOOP:
            $aBusyChild = $Pool->getBusyChildren();
            if (!empty($aBusyChild)) {
                foreach ($aBusyChild as $Child) {
                    $sResultMessage = $Child->receiveMessage();
                    if ($sResultMessage !== false) {
                        $Pool->markChildIdle($Child);
                    }
                }
            }
            usleep($this->iLoopSleepUS);
        }
    }

    /**
     * @return string|null
     */
    abstract protected function getMessage();
}

