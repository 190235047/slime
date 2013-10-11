<?php
namespace Slime\Bundle\Framework;

use Slime\Component\Route\CallBack;

/**
 * Class Controller_Cli
 * Slime 内置Cli控制器基类
 *
 * @package Slime\Core
 * @author  smallslime@gmail.com
 */
abstract class Controller_Cli
{
    /**
     * @var Context 上下文对象
     */
    protected $Context;

    /**
     * @var \Slime\Component\Log\Logger 日志对象
     */
    protected $Log;

    /**
     * @var \Slime\Component\Config\Configure 配置对象
     */
    protected $Config;

    /**
     * @var array 参数数组
     */
    protected $aParam;

    public function __construct(array $aParam = array())
    {
        $this->Context = $Context = Context::getInst();
        $this->Log     = $Context->Log;
        $this->Config  = $Context->Config;
        $this->aParam  = $aParam;
    }

    public function innerCall($sController, $sMethod, $aParam = null)
    {
        if ($aParam===null) {
            $aParam = $this->aParam;
        }
        $CallBack = new CallBack($this->Context->sNS, $this->Log);
        $CallBack->setCBObject(
            $sController,
            $sMethod,
            $aParam
        );
        $CallBack->call();
    }
}