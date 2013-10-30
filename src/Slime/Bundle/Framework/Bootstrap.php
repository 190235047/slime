<?php
namespace Slime\Bundle\Framework;

use Slime\Component\Config;
use Slime\Component\I18N\I18N;
use Slime\Component\Log;
use Slime\Component\Route;
use Slime\Component\Http;

/**
 * Class Bootstrap
 *
 * 框架核心运行类
 * 1. 调用静态方法 factory
 *    1. 生成上下文对象;
 *    2. 注册各种变量/对象到上下文对象 详见 Slime\Bundle\Framework\Context;
 *    3. 注册 ErrorHandle 方法
 * 2. 调用 run 方法运行
 *    1. 路由, 获取回调对象
 *    2. 执行回调
 *
 * @package Slime\Bundle\Framework
 * @author  smallslime@gmail.com
 */
class Bootstrap
{
    /**
     * @var Context
     */
    protected $Context;

    /**
     * @var array
     */
    private $aSysConfig;

    /**
     * @param string $sENV        当前环境(例如 publish:生产环境; development:开发环境)
     * @param string $sDirConfig  配置文件目录
     * @param string $sDirLang    语言文件目录
     * @param string $sAppNs      应用的命名空间
     * @param string $sAPI        PHP运行方式, 当前支持 (cli||http)
     * @param array  $aLogConfig  Log初始化配置, 详见 Slime\Component\Log\ReadMe.md
     *
     * @throws \Exception
     *
     * @return \Slime\Bundle\Framework\Bootstrap
     */
    public static function factory($sENV, $sDirConfig, $sDirLang, $sAppNs, $sAPI, array $aLogConfig)
    {
        # register self
        $SELF = new self();

        # set error handle
        set_error_handler(array('Slime\Bundle\Framework\Bootstrap', 'handleError'));

        # get context object from current request
        Context::makeInst();
        $Context = Context::getInst();

        # register execute datetime object
        $Context->register('DateTime', new \DateTime());

        # register s_api
        $Context->register('sRunMode', $sRunMode = (strtolower($sAPI) === 'cli' ? 'cli' : 'http'));

        #register env
        $Context->register('sENV', $sENV);

        #register app namespace
        $Context->register('sNS', $sAppNs);

        # register logger
        if (!isset($aLogConfig[$sRunMode])) {
            throw new \Exception('There is no log config to match current runtime:[' . $sRunMode . ']');
        }
        $aWriter = array();
        foreach ($aLogConfig[$sRunMode]['writer'] as $mKey => $mV) {
            if (is_int($mKey) && is_string($mV)) {
                $sClassName = $mV[0] === '@' ? '\\Slime\\Component\\Log\\Writer_' . substr($mV, 1) : $mV;
                $mV         = array();
            } else {
                $sClassName = $mKey[0] === '@' ? '\\Slime\\Component\\Log\\Writer_' . substr($mKey, 1) : $mV;
            }
            $Ref       = new \ReflectionClass($sClassName);
            $aWriter[] = $Ref->newInstanceArgs($mV);
        }
        $Log = new Log\Logger($aWriter, $aLogConfig[$sRunMode]['level']);
        $Context->register('Log', $Log);

        # register configure
        $Config = Config\Configure::factory(
            '@PHP',
            $sDirConfig . '/' . $sENV,
            $sDirConfig . '/publish'
        );
        $Context->register('Config', $Config);

        # get system config
        $SELF->aSysConfig = $Config->get('system', null, true);

        # register router
        $Context->register('Route', new Route\Router($sAppNs, $Log));

        # register i18N
        $Context->register('I18N', new I18N($sDirLang));

        $Context->register('Bootstrap', $SELF);
        $SELF->Context = $Context;

        return $SELF;
    }

    private function __construct()
    {
    }

    public function __clone()
    {
        throw new \Exception('Can not clone');
    }

    public function run()
    {
        $sMethod = 'run' . $this->Context->sRunMode;
        try {
            $this->$sMethod();
        } catch (\Exception $E) {
            if (isset($this->aSysConfig['exception_handle'])) {
                call_user_func($this->aSysConfig['exception_handle'], $E);
            } else {
                throw $E;
            }
        }
    }

    protected function runHttp()
    {
        #register http request and response
        if ($this->Context->isRegister('HttpRequest')) {
            $HttpRequest = $this->Context->HttpRequest;
        } else {
            $HttpRequest = Http\HttpRequest::createFromGlobals();
            $this->Context->register('HttpRequest', $HttpRequest);
        }
        $HttpResponse = Http\HttpResponse::create()->setNoCache();
        $this->Context->register('HttpResponse', $HttpResponse);

        # run route
        $aCallBack = $this->Context->Route->generateFromHttp(
            $HttpRequest,
            $HttpResponse,
            $this->aSysConfig['route_http']
        );
        if (!empty($aCallBack)) {
            foreach ($aCallBack as $CallBack) {
                $this->Context->register('CallBack', $CallBack);
                $CallBack->call();
            }
        }

        # response
        $HttpResponse->send();
    }

    protected function runCli()
    {
        $aCallBack = $this->Context->Route->generateFromCli(
            $GLOBALS['argv'],
            $this->aSysConfig['route_cli']
        );
        if (!empty($aCallBack)) {
            foreach ($aCallBack as $CallBack) {
                $this->Context->register('CallBack', $CallBack);
                $CallBack->call();
            }
        }
    }

    /**
     * @param int    $iErrNum     错误码
     * @param string $sErrStr     错误信息
     * @param string $sErrFile    错误发生文件
     * @param int    $iErrLine    错误发生行
     * @param string $sErrContext 错误发生上下文
     */
    public static function handleError($iErrNum, $sErrStr, $sErrFile, $iErrLine, $sErrContext)
    {
        $sStr = $iErrNum . ':' . $sErrStr . "\nIn File[$sErrFile]:Line[$iErrLine]";

        $Context = Context::getInst();
        # 在某些对象, 在PHP全局回收时调用析构函数. 此时 Context 已经销毁, 如果析构函数中发生错误, 会拿不到 Context. 尽量避免!
        if ($Context===null) {
            trigger_error($sStr, $iErrNum);
        } else {
            switch ($iErrNum) {
                case E_NOTICE:
                case E_USER_NOTICE:
                    $Context->Log->notice($sStr);
                    break;
                case E_USER_ERROR:
                    $Context->Log->error($sStr);
                    exit(1);
                    break;
                default:
                    $Context->Log->warning($sStr);
                    break;
            }
        }
    }
}
