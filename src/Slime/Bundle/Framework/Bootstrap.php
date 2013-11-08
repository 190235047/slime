<?php
namespace Slime\Bundle\Framework;

use Slime\Component\Config;
use Slime\Component\I18N;
use Slime\Component\Log;
use Slime\Component\Route;
use Slime\Component\Http;

/**
 * Class Bootstrap
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
abstract class Bootstrap
{
    /**
     * @var Context
     */
    protected $Context;

    abstract protected function getAppDir();

    /**
     * @param string                      $sAPI                  PHP运行方式, 当前支持 (cli||http)
     * @param string                      $sENV                  当前环境(例如 publish:生产环境; development:开发环境)
     * @param string                      $sAppNs                应用的命名空间
     * @param array                       $mLogConfigOrLogObject 日志对象初始化配置
     * @param Http\HttpRequest|array|null $mIn
     */
    final public function __construct($sAPI, $sENV, $sAppNs, $mLogConfigOrLogObject, $mIn = null)
    {
        set_error_handler(array($this, 'handleError'));

        Context::makeInst();
        $this->Context = $Context = Context::getInst();

        # run mode
        $sRunMode = (strtolower($sAPI) === 'cli' ? 'cli' : 'http');

        # register logger
        if ($mLogConfigOrLogObject instanceof Log\Logger) {
            $Context->register('Log', $mLogConfigOrLogObject);
        } else {
            $mLogConfigOrLogObject = $mLogConfigOrLogObject[$sRunMode];
            $aWriter               = array();
            foreach ($mLogConfigOrLogObject['writer'] as $mKey => $mV) {
                if (is_int($mKey) && is_string($mV)) {
                    $sClass    = $mV[0] === '@' ?
                        '\\Slime\\Component\\Log\\Writer_' . substr($mV, 1) :
                        $mV;
                    $aWriter[] = new $sClass();
                } else {
                    $sClass    = $mKey;
                    $aParam    = $mV;
                    $Ref       = new \ReflectionClass(
                        $sClass[0] === '@' ?
                            '\\Slime\\Component\\Log\\Writer_' . substr($sClass, 1) :
                            $sClass
                    );
                    $aWriter[] = $Ref->newInstanceArgs($aParam);
                }
            }
            $Log = new Log\Logger($aWriter, isset($mLogConfigOrLogObject['level']) ? $mLogConfigOrLogObject['level'] : Log\Logger::LEVEL_ALL);
            $Context->register('Log', $Log);
        }

        # register app_dir
        $Context->register('aAppDir', $this->getAppDir());

        # register run_mode
        $Context->register('sRunMode', $sRunMode);

        #register env
        $Context->register('sENV', $sENV);

        #register app namespace
        $Context->register('sNS', $sAppNs);

        # register this
        $Context->register('Bootstrap', $this);

        # register datetime
        $Context->register('DateTime', new \DateTime());

        # register config
        $sDirConfig = $this->Context->aAppDir['config'];
        $Config     = Config\Configure::factory(
            '@PHP',
            $sDirConfig . '/' . $this->Context->sENV,
            $sDirConfig . '/publish'
        );
        $this->Context->register('Config', $Config);

        # register http / argv
        if ($sRunMode === 'http') {
            $this->Context->register(
                'HttpRequest',
                $mIn instanceof Http\HttpRequest ? $mIn : Http\HttpRequest::createFromGlobals()
            );
            $this->Context->register('HttpResponse', Http\HttpResponse::create()->setNoCache());

            # register i18n
            $aI18NConfig = $this->Context->aAppDir;
            if (isset($aI18NConfig['i18n'])) {
                # register I18N
                $this->Context->register(
                    'I18N',
                    I18N\I18N::createFromHttp($aI18NConfig['i18n'], $this->Context->HttpRequest)
                );
            }
        } else {
            $this->Context->register(
                'aArgv',
                is_array($mIn) ? $mIn : $GLOBALS['argv']
            );
        }

        # register route
        $this->Context->register('Route', new Route\Router($this->Context->sNS));

        # register custom
        $Ref     = new \ReflectionClass($this);
        $aMethod = $Ref->getMethods(\ReflectionMethod::IS_PROTECTED ^ \ReflectionMethod::IS_ABSTRACT);
        if (!empty($aMethod)) {
            $sTip = 'regOn' . ucfirst($sRunMode);
            foreach ($aMethod as $Method) {
                $aArr = explode('_', $Method->name, 2);
                if ($aArr[0] === 'reg' || $aArr[0] == $sTip) {
                    $this->{$Method->name}();
                }
            }
        }
    }

    public function run()
    {
        $sMethod = 'run' . $this->Context->sRunMode;
        try {
            $this->$sMethod();
        } catch (\Exception $E) {
            $mExceptionHandle = $this->Context->Config->get('system.uncaught_exception_handle');
            if ($mExceptionHandle !== null) {
                call_user_func($mExceptionHandle, $E);
            } else {
                $this->Context->Log->error($E->getMessage());
            }
            exit(1);
        }
    }

    protected function runHttp()
    {
        # run route
        $aCallBack = $this->Context->Route->generateFromHttp(
            $this->Context->HttpRequest,
            $this->Context->HttpResponse,
            $this->Context->Config->get('route.http')
        );
        if (!empty($aCallBack)) {
            foreach ($aCallBack as $CallBack) {
                $this->Context->register('CallBack', $CallBack);
                $CallBack->call();
            }
        }

        # response
        $this->Context->HttpResponse->send();
    }

    protected function runCli()
    {
        $aCallBack = $this->Context->Route->generateFromCli(
            $this->Context->aArgv,
            $this->Context->Config->get('route.cli')
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
    public function handleError($iErrNum, $sErrStr, $sErrFile, $iErrLine, $sErrContext)
    {
        $sStr = $iErrNum . ':' . $sErrStr . "\nIn File[$sErrFile]:Line[$iErrLine]";

        $Context = Context::getInst();
        # 在某些对象, 在PHP全局回收时调用析构函数. 此时 Context 已经销毁, 如果析构函数中发生错误, 会拿不到 Context. 尽量避免!
        if ($Context === null || !$Context->isRegister('Log')) {
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

    final public function __clone()
    {
        throw new \Exception('Can not clone');
    }
}
