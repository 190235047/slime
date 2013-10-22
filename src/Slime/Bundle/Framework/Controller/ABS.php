<?php
namespace Slime\Bundle\Framework;

use Slime\Component\Log\Logger;
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
     * @param array $aToRegContext //@todo
     *
     * @return mixed
     */
    public function outerCallAsCli($aArgs, array &$aToRegContext = array())
    {
        if (isset($GLOBALS['argv'])) {
            $mArgvBak = $GLOBALS['argv'];
        }
        $GLOBALS['argv'] = $aArgs;

        # 运行
        Bootstrap::factory(
            $this->Context->sENV,
            DIR_CONFIG,
            $this->Context->sNS,
            'cli',
            array(
                'cli'  => array(
                    'writer' => array('@ECHO'),
                    'level'  => Logger::LEVEL_ALL
                ),
            )
        )->run();

        Context::getInst()->destroy();

        if (isset($mArgvBak)) {
            $GLOBALS['argv'] = $mArgvBak;
        }

        return ;
    }

    /**
     * @param Http\HttpRequest $HttpRequest
     * @param array            $aToRegContext //@todo
     *
     * @return mixed
     */
    public function outerCallAsHttp(Http\HttpRequest $HttpRequest, array &$aToRegContext = array())
    {
        /*
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
        */
    }
}