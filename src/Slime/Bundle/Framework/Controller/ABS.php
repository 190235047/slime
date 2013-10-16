<?php
namespace Slime\Bundle\Framework;

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

    public function innerCall($sController, $sMethod, $aParam = null)
    {
        if ($aParam === null) {
            $aParam = $this->aParam;
        }
        $CallBack = new Route\CallBack($this->Context->sNS, $this->Log);
        $CallBack->setCBObject(
            $sController,
            $sMethod,
            $aParam
        );
        $CallBack->call();
        return $CallBack->mCallable->aData;
    }

    /**
     * @param array $aArgs
     * @param array $aNeedContextVarList //@todo
     *
     * @return mixed
     */
    public function outerCallAsCli($aArgs, array &$aNeedContextVarList = array())
    {
        $mArgvBak        = $GLOBALS['argv'];
        $GLOBALS['argv'] = $aArgs;

        # 获取一个 Context 副本
        $Context = clone $this->Context;

        # 运行
        Bootstrap::factoryWithContext($Context)->run();
        $aResult = $Context->CallBack->mCallable->aData;
        $Context->destroy();

        $GLOBALS['argv'] = $mArgvBak;

        return $aResult;
    }

    /**
     * @param Http\HttpRequest $HttpRequest
     * @param array            $aNeedContextVarList //@todo
     *
     * @return mixed
     */
    public function outerCallAsHttp(Http\HttpRequest $HttpRequest, array &$aNeedContextVarList = array())
    {
        # 获取一个 Context 副本
        $Context = clone $this->Context;

        # 复写原始 HttpRequest
        $HttpRequest = clone $HttpRequest;
        $Context->register('HttpRequest', $HttpRequest);

        # 运行
        Bootstrap::factoryWithContext($Context)->run();
        $aResult = $Context->CallBack->mCallable->aData;
        $Context->destroy();

        return $aResult;
    }
}