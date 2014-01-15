<?php
namespace Slime\Bundle\Framework;

use Slime\Component\Config\IAdaptor;
use Slime\Component\Http\HttpRequest;
use Slime\Component\Http\HttpResponse;
use Slime\Component\Route\Router;

class Bootstrap
{
    # error deal
    private static $mCBErrorHandle = null;
    private static $mCBUncaughtException = null;

    public static function setHandle($mCBErrorHandle = null, $mCBUncaughtException = null)
    {
        self::$mCBErrorHandle = $mCBErrorHandle === null ?
            array('Slime\\Bundle\\Framework\\Bootstrap', 'handleError') :
            $mCBErrorHandle;

        self::$mCBUncaughtException = $mCBUncaughtException === null ?
            array('Slime\\Bundle\\Framework\\Bootstrap', 'handleUncaughtException') :
            $mCBUncaughtException;
    }

    public static function handleUncaughtException(\Exception $E)
    {
        trigger_error($E->getMessage(), E_USER_ERROR);
    }

    public static function handleError($iErrNum, $sErrStr, $sErrFile, $iErrLine, $sErrContext)
    {
        $sStr = $iErrNum . ':' . $sErrStr . "\nIn File[$sErrFile]:Line[$iErrLine]";

        $Context = Context::getInst();
        # 在某些对象, 在PHP全局回收时调用析构函数. 此时 Context 已经销毁, 如果析构函数中发生错误, 会拿不到 Context. 尽量避免!
        if ($Context === null || !$Context->isRegister('Log')) {
            trigger_error($sStr, E_USER_WARNING);
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

    /**
     * @param string       $sENV
     * @param string       $sAppNs
     * @param IAdaptor     $Config
     * @param null|Context $Context
     * @param null|string  $sAPI
     * @param null|mixed   $mHttpReqOrCliArg
     * @param string       $sModuleConfigKey
     */
    public function __construct(
        $sENV,
        $sAppNs,
        IAdaptor $Config,
        $Context = null,
        $sAPI = null,
        $mHttpReqOrCliArg = null,
        $sModuleConfigKey = 'module'
    ) {
        /** @var Context $Context */
        if ($Context === null || !$Context instanceof Context) {
            Context::makeInst();
            $Context = Context::getInst();
        }
        $this->Context = $Context;

        # register
        $aMap = array(
            'Bootstrap' => $this,
            'sENV'      => $sENV,
            'sNS'       => $sAppNs,
            'Config'    => $Config,
            'Route'     => new Router($sAppNs),
            'sRunMode'  => $sAPI === null ?
                    (strtolower(PHP_SAPI) === 'cli' ? 'cli' : 'http') :
                    (strtolower($sAPI) === 'cli' ? 'cli' : 'http'),
        );

        if ($aMap['sRunMode'] === 'cli') {
            $aMap['aArgv'] = is_array($mHttpReqOrCliArg) ?
                $mHttpReqOrCliArg :
                $GLOBALS['argv'];
        } else {
            $aMap['HttpRequest']  = $mHttpReqOrCliArg instanceof HttpRequest ?
                $mHttpReqOrCliArg :
                HttpRequest::createFromGlobals();
            $aMap['HttpResponse'] = HttpResponse::create();
        }

        $Context->registerMulti($aMap);
    }

    public function run()
    {
        $sMethod = 'run' . $this->Context->sRunMode;
        if (self::$mCBUncaughtException===null) {
            $this->$sMethod();
        } else {
            try {
                $this->$sMethod();
            } catch (\Exception $E) {
                call_user_func(self::$mCBUncaughtException, $E);
                exit(1);
            }
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
}