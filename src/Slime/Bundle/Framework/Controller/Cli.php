<?php
namespace Slime\Bundle\Framework;

/**
 * Class Controller_Cli
 * Slime 内置Cli控制器基类
 *
 * @package Slime\Bundle\Framework
 * @author  smallslime@gmail.com
 */
abstract class Controller_Cli extends Controller_ABS
{
    public function __construct(array $aParam = array())
    {
        $this->Context = $Context = Context::getInst();
        $this->Log     = $Context->Log;
        $this->Config  = $Context->Config;
        $this->aParam  = $aParam;
    }
}