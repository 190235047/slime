<?php
namespace SlimeFramework\Component\MultiProcess;

use Psr\Log\LoggerInterface;

abstract class Task
{
    public function __construct($sMessage, LoggerInterface $Log)
    {
        $this->sMessage = $sMessage;
        $this->Logger   = $Log;
    }

    abstract public function run();
}