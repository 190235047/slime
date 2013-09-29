<?php
namespace SlimeFramework\Core;

/**
 * Class Controller_Cli
 * SlimeFramework 内置Cli控制器基类
 *
 * @package SlimeFramework\Core
 * @author  smallslime@gmail.com
 */
abstract class Controller_Cli
{
    /**
     * @var Context 上下文对象
     */
    protected $Context;

    /**
     * @var \SlimeFramework\Component\Log\Logger 日志对象
     */
    protected $Log;

    /**
     * @var \SlimeFramework\Component\Config\Configure 配置对象
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
}