<?php
namespace Slime\Bundle\Framework;

use Slime\Component\Log;
use Slime\Component\Route;
use Slime\Component\Http;

/**
 * Class Controller_Cli
 * Slime 内置控制器基类
 *
 * @package Slime\Bundle\Framework
 * @author  smallslime@gmail.com
 */
abstract class Controller_ABS
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
        $this->aParam  = $aParam;
        $this->Context = Context::getInst();
        $this->Log     = $this->Context->Log;
        $this->Config  = $this->Context->Config;
    }

    public function innerCall($sController, $sMethod, $aParam = null)
    {
        if ($aParam === null) {
            $aParam = $this->aParam;
        }
        $CallBack = new Route\CallBack($this->Context->sNS);
        $CallBack->setCBObject($sController, $sMethod, $aParam);
        $CallBack->call();
        return $CallBack->mCallable->aData;
    }

    /**
     * @param array                 $aArgs
     * @param array|Log\Logger|null $mLogConfigOrLogObject
     *
     * @return mixed
     */
    public function outerCallAsCli($aArgs, $mLogConfigOrLogObject = null)
    {
        # pre
        $sBootstrap = get_class($this->Context->Bootstrap);
        if ($mLogConfigOrLogObject===null) {
            $mLogConfigOrLogObject = $this->Log;
        } elseif (is_array($mLogConfigOrLogObject) && !isset($mLogConfigOrLogObject['cli'])) {
            $mLogConfigOrLogObject = array('cli' => $mLogConfigOrLogObject);
        }

        /** @var Bootstrap $Bootstrap */
        $Bootstrap = new $sBootstrap(
            'cli',
            $this->Context->sENV,
            $this->Context->sNS,
            $mLogConfigOrLogObject,
            $aArgs
        );
        $Bootstrap->run();
        Context::destroy();
    }

    /**
     * @param Http\HttpRequest      $HttpRequest
     * @param array|Log\Logger|null $mLogConfigOrLogObject
     */
    public function outerCallAsHttp(
        Http\HttpRequest $HttpRequest,
        $mLogConfigOrLogObject = null
    )
    {
        # pre
        $sBootstrap = get_class($this->Context->Bootstrap);
        if ($mLogConfigOrLogObject===null) {
            $mLogConfigOrLogObject = $this->Log;
        } elseif (is_array($mLogConfigOrLogObject) && !isset($mLogConfigOrLogObject['http'])) {
            $mLogConfigOrLogObject = array('http' => $mLogConfigOrLogObject);
        }

        /** @var Bootstrap $Bootstrap */
        $Bootstrap = new $sBootstrap(
            'http',
            $this->Context->sENV,
            $this->Context->sNS,
            $mLogConfigOrLogObject,
            $HttpRequest
        );
        $Bootstrap->run();
        Context::destroy();
    }
}