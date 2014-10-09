<?php
namespace Slime\Bundle\Framework;

use Slime\Component\Config\IAdaptor;
use Slime\Component\Http\HttpRequest;
use Slime\Component\Http\HttpResponse;
use Slime\Component\Route\RouteFailException;
use Slime\Component\Route\Router;

class Bootstrap
{
    # error deal
    private static $mCBUncaughtException = array('Slime\\Bundle\\Framework\\Bootstrap', 'handleUncaughtException');

    public static function setHandle(
        $mCBErrorHandle = array('Slime\\Bundle\\Framework\\Bootstrap', 'handleError'),
        $iErrType = null,
        $mCBUncaughtException = null
    ) {
        set_error_handler(
            $mCBErrorHandle,
            $iErrType === null ? (E_ALL | E_STRICT) : (int)$iErrType
        );

        if ($mCBUncaughtException !== null) {
            self::$mCBUncaughtException = $mCBUncaughtException;
        }
    }

    public static function setDefaultErrorPage()
    {
        $C = Context::getInst();
        if ($C->sRunMode != 'http') {
            return;
        }
        $RES = $C->HttpResponse;
        $C->register(
            'mCBErrPage',
            function (\Exception $E) use ($RES) {
                $aArr = $E->getTrace();
                foreach ($aArr as $iK => $aItem) {
                    if (isset($aItem['args'])) {
                        unset($aArr[$iK]['args']);
                    }
                }
                $RES->setBody(
                    sprintf(
                        '<h1>%s</h1><h2>%d:%s</h2><h3>File:%s;Line:%s</h3><div><pre>%s</pre></div>',
                        get_class($E),
                        $E->getCode(),
                        $E->getMessage(),
                        $E->getFile(),
                        $E->getLine(),
                        var_export($aArr, true)
                    )
                );
            }
        );
    }

    public static function handleUncaughtException(\Exception $E)
    {
        $C    = Context::getInst();
        $sStr = $E->getMessage();
        # 在某些对象的析构函数中使用了Context, 而对象在脚本执行完成时进入回收阶段, 才调用对象的析构.
        # 此时 Context 可能已经销毁, 所以会拿不到 Context. 尽量避免!
        if ($C === null || !$C->isRegistered('Log', true)) {
            trigger_error($sStr, E_USER_ERROR);
        } else {
            $C->Log->error($E->getMessage());
            if ($C->sRunMode === 'http') {
                if ($C->HttpResponse->getResponseCode() < 400) {
                    $C->HttpResponse->setResponseCode(500);
                }
                if ($C->isRegistered('mCBErrPage', true)) {
                    call_user_func($C->mCBErrPage, $E);
                }
                $C->HttpResponse->send();
            }
        }
        exit(1);
    }

    public static function handleError($iErrNum, $sErrStr, $sErrFile, $iErrLine, $sErrContext)
    {
        $sStr = $iErrNum . ':' . $sErrStr . "\nIn File[$sErrFile]:Line[$iErrLine]";

        $C = Context::getInst();
        # 在某些对象的析构函数中使用了Context, 而对象在脚本执行完成时进入回收阶段, 才调用对象的析构.
        # 此时 Context 可能已经销毁, 所以会拿不到 Context. 尽量避免!
        if ($C === null || !$C->isRegistered('Log', true)) {
            trigger_error($sStr, E_USER_WARNING);
        } else {
            switch ($iErrNum) {
                case E_NOTICE:
                case E_USER_NOTICE:
                    $C->Log->notice($sStr);
                    break;
                case E_USER_ERROR:
                    throw new \ErrorException($sStr);
                default:
                    $C->Log->warning($sStr);
                    break;
            }
        }
    }

    /**
     * @var \Slime\Bundle\Framework\Context
     */
    public $Context;

    /**
     * @param IAdaptor    $Config
     * @param string      $sENV
     * @param mixed       $mHttpReqOrCliArg
     * @param null|string $sAPI
     */
    public function __construct(
        $Config,
        $sENV,
        $mHttpReqOrCliArg = null,
        $sAPI = null
    ) {
        Context::makeInst();
        $this->Context = Context::getInst();

        # register
        $aMap = array(
            'Bootstrap' => $this,
            'sENV'      => $sENV,
            'Config'    => $Config,
            'Route'     => new Router($this->Context),
            'sRunMode'  => $sAPI === null ?
                    (strtolower(PHP_SAPI) === 'cli' ? 'cli' : 'http') :
                    (strtolower($sAPI) === 'cli' ? 'cli' : 'http'),
            'Arr'       => array()
        );

        if ($aMap['sRunMode'] === 'cli') {
            $aMap['aArgv'] = $mHttpReqOrCliArg === null ?
                $GLOBALS['argv'] :
                $mHttpReqOrCliArg;
        } else {
            if ($mHttpReqOrCliArg===null) {
                $aMap['HttpRequest'] = new HttpRequest();
            } else {
                $aMap['HttpRequest'] = $mHttpReqOrCliArg;
            }
            $aMap['HttpResponse'] = new HttpResponse();
        }

        $this->Context->registerMulti($aMap);
    }

    /**
     * @param string $sRouteKey Route file name using by get from config
     */
    public function run($sRouteKey = null)
    {
        $fT1 = microtime(true);
        try {
            $C = $this->Context;
            $Log = $C->Log;
            switch ($C->sRunMode) {
                case 'http':
                    $REQ = $C->HttpRequest;
                    $RES = $C->HttpResponse;
                    # add log
                    $Log->info(sprintf('RUN_START : %s : %s', $REQ->getRequestMethod(), $REQ->getRequestURI()));
                    # run route
                    $aCallBack = $C->Route->generateFromHttp(
                        $REQ,
                        $RES,
                        $C->Config->get($sRouteKey === null ? 'route.http' : $sRouteKey),
                        $bHitMain
                    );
                    if (!$bHitMain) {
                        throw new RouteFailException("[MAIN] : Current request is not hit any router");
                    }
                    foreach ($aCallBack as $CallBack) {
                        $C->register('CallBack', $CallBack);
                        $CallBack->call();
                    }

                    # response
                    $RES->send();
                    break;
                case 'cli':
                    # add log
                    $Log->info('RUN_START');

                    $aCallBack = $C->Route->generateFromCli(
                        $C->aArgv,
                        $C->Config->get($sRouteKey === null ? 'route.cli' : $sRouteKey),
                        $bHitMain
                    );
                    if (!$bHitMain) {
                        throw new RouteFailException("[MAIN] : Current request is not hit any router");
                    }
                    foreach ($aCallBack as $CallBack) {
                        $C->register('CallBack', $CallBack);
                        $CallBack->call();
                    }
                    break;
                default:
                    throw new \RuntimeException("[MAIN] : RunMode {$this->Context->sRunMode} is not supported");
            }
        } catch (RouteFailException $E) {
            $this->Context->HttpResponse->setResponseCode(404);
            call_user_func(self::$mCBUncaughtException, $E);
            exit(1);
        } catch (\Exception $E) {
            call_user_func(self::$mCBUncaughtException, $E);
            exit(1);
        }
        $Log->info('RUN_END : ' . round(microtime(true) - $fT1, 6));
    }
}