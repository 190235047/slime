<?php
namespace SlimeFramework\Core;

use SlimeFramework\Component\Config;
use SlimeFramework\Component\Log;
use SlimeFramework\Component\Route;
use SlimeFramework\Component\Http;

/**
 * Class Bootstrap
 * @package Slime
 * @author smallslime@gmail.com
 * @version 1.0
 *
 */
class Bootstrap
{
    /**
     * @var Context
     */
    protected $Context;

    /**
     * @param string $sENV
     * @param string $sDirConfig
     * @param string $sAppNs
     * @param string $sRunMode (cli||http)
     * @param array $aLogConfig
     *
     * @return $this
     */
    public static function factory($sENV, $sDirConfig, $sAppNs, $sRunMode, array $aLogConfig)
    {
        # get context object from current request
        Context::makeInst();
        $Context = Context::getInst();

        # register execute datetime object
        $Context->register('DateTime', new \DateTime());

        # register s_api
        $Context->register('sRunMode', $sRunMode);

        #register env
        $Context->register('sENV', $sENV);

        #register app namespace
        $Context->register('sNS', $sAppNs);

        # register logger
        if (!isset($aLogConfig[$sRunMode])) {
            trigger_error('There is no log config to match current runtime:[' . $sRunMode . ']', E_USER_ERROR);
            exit(1);
        }
        $aWriter = array();
        foreach ($aLogConfig[$sRunMode] as $mKey => $mV) {
            if (is_int($mKey) && is_string($mV)) {
                $sClassName = $mV[0]==='@' ? '\\SlimeFramework\\Component\\Log\\Writer_' . substr($mV, 1) : $mV;
                $mV = array();
            } else {
                $sClassName = $mKey[0]==='@' ? '\\SlimeFramework\\Component\\Log\\Writer_' . substr($mKey, 1) : $mV;
            }
            $Ref = new \ReflectionClass($sClassName);
            $aWriter[] = $Ref->newInstanceArgs($mV);
        }
        $Log = new Log\Logger($aWriter);
        $Context->register('Log', $Log);

        # register configure
        $Config = new Config\Configure('@PHP',
            $sDirConfig . '/' . $sENV,
            $sDirConfig . '/publish',
            $Log
        );
        $Context->register('Config', $Config);

        # register router
        $Context->register('Route', new Route\Router($sAppNs, $Log));

        # register self
        $SELF = new self();
        $Context->register('Bootstrap', $SELF);
        $SELF->Context = $Context;

        # set error handle
        set_error_handler(array($Context->Bootstrap, 'handleError'));

        return $Context->Bootstrap;
    }

    public function run()
    {
        $sMethod = 'run' . $this->Context->sRunMode;
        $this->$sMethod();
    }

    public function runHttp()
    {
        #register http request and response
        $HttpRequest = Http\Request::createFromGlobals();
        $HttpResponse = Http\Response::factory()->setNoCache();
        $this->Context->register('HttpRequest', $HttpRequest);
        $this->Context->register('HttpResponse', $HttpResponse);

        # run route
        $CallBack = Context::getInst()->Route->generateFromHttp(
            $HttpRequest,
            $this->Context->Config->get('system.route')
        );
        $this->Context->register('CallBack', $CallBack);
        $CallBack->call();

        # response
        $HttpResponse->send();
    }

    public function runCli()
    {
        ;
    }

    public function handleError($iErrNum, $sErrStr, $sErrFile, $iErrLine, $sErrContext)
    {
        $sStr = $iErrNum . ':' . $sErrStr . "\nIn File[$sErrFile]:Line[$iErrLine]";
        switch ($iErrNum) {
            case E_NOTICE:
            case E_USER_NOTICE:
                Context::getInst()->Log->notice($sStr);
                break;
            case E_USER_ERROR:
                Context::getInst()->Log->error($sStr);
                exit(1);
                break;
            default:
                Context::getInst()->Log->warning($sStr);
                break;
        }
    }
}
