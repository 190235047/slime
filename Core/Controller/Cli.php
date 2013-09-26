<?php
namespace SlimeFramework\Core;

abstract class Controller_Cli
{
    protected $Context;

    protected $aData = array();

    public function __construct(array $aParam = array())
    {
        $this->Context = $Context = Context::getInst();
        $this->Log     = $Context->Log;
        $this->Config  = $Context->Config;
        $this->aParam  = $aParam;
    }
}