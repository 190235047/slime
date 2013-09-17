<?php
namespace SlimeFramework\Component\MultiProcess;

use SlimeFramework\Component\Log\Logger;

abstract class Task
{
    public function __construct($sMessage, Logger $Logger)
    {
        $this->sMessage = $sMessage;
        $this->Logger   = $Logger;
    }

    abstract public function run();
}