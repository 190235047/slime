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
        # 在某些对象的析构函数中使用了Context, 而对象在脚本执行完成时进入回收阶段, 才调用对象的析构.
        # 此时 Context 可能已经销毁, 所以会拿不到 Context. 尽量避免!
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
     * @param string      $sENV
     * @param string      $sAppNs
     * @param IAdaptor    $Config
     * @param null|mixed  $mHttpReqOrCliArg
     * @param null|string $sAPI
     */
    public function __construct(
        $sENV,
        $sAppNs,
        IAdaptor $Config,
        $mHttpReqOrCliArg = null,
        $sAPI = null
    ) {
        Context::makeInst();
        $this->Context = Context::getInst();

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

        $this->Context->registerMulti($aMap);
    }

    /**
     * @param string $sRouteKey      Route file name using by get from config
     * @param string $sControllerPre Null means default and string means custom
     */
    public function run($sRouteKey = null, $sControllerPre = null)
    {
        $sMethod = 'run' . $this->Context->sRunMode;
        if ($sControllerPre !== null) {
            $this->Context->Route->sControllerPre = (string)$sControllerPre;
        }

        if (self::$mCBUncaughtException === null) {
            $this->$sMethod($sRouteKey);
        } else {
            try {
                $this->$sMethod($sRouteKey);
            } catch (\Exception $E) {
                call_user_func(self::$mCBUncaughtException, $E);
                exit(1);
            }
        }
    }

    protected function runHttp($sRouteKey)
    {
        # run route
        $aCallBack = $this->Context->Route->generateFromHttp(
            $this->Context->HttpRequest,
            $this->Context->HttpResponse,
            $this->Context->Config->get($sRouteKey === null ? 'route.http' : $sRouteKey)
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

    protected function runCli($sRouteKey)
    {
        $aCallBack = $this->Context->Route->generateFromCli(
            $this->Context->aArgv,
            $this->Context->Config->get($sRouteKey === null ? 'route.cli' : $sRouteKey)
        );
        if (!empty($aCallBack)) {
            foreach ($aCallBack as $CallBack) {
                $this->Context->register('CallBack', $CallBack);
                $CallBack->call();
            }
        }
    }
}