<?php
namespace SlimeFramework\Component\MultiProcess;

use SlimeFramework\Component\Log\Logger;

abstract class Job
{
    public function __construct($sMessage, Logger $Logger)
    {
        $this->sMessage = $sMessage;
        $this->Logger   = $Logger;
    }

    abstract public function run();
}