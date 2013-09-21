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
     *
     * @return $this
     */
    public static function factory($sENV, $sDirConfig, $sAppNs, $sRunMode)
    {
        # get context object from current request
        $Context = Context::getInst();

        # register execute datetime object
        $Context->register('DateTime', new \DateTime());

        # register s_api
        $Context->register('sRunMode', $sRunMode);

        #register env
        $Context->register('sENV', $sENV);

        #register app namespace
        $Context->register('sNS', $sAppNs);

        # register configure
        $Config = new Config\Configure($sDirConfig . '/' . $sENV, $sDirConfig . '/' . 'publish');
        $Context->register('Config', $Config);

        # register logger
        $aLogConfig = $Config->get('system.log');
        if (!isset($aLogConfig[$sRunMode])) {
            trigger_error('There is no log config to match current runtime:[' . $sRunMode . ']', E_USER_ERROR);
            exit(1);
        }
        $aWriter = array();
        foreach ($aLogConfig[$sRunMode] as $sWriter => $aArg) {
            if (is_int($sWriter) && is_string($aArg)) {
                $sClassName = '\\Slime\\Log\\Writer_' . $aArg;
                $aArg = array();
            }  else {
                $sClassName = '\\Slime\\Log\\Writer_' . $sWriter;
            }
            $Ref = new \ReflectionClass($sClassName);
            $aWriter[] = $Ref->newInstanceArgs($aArg);
        }
        $Context->register('Log', new Log\Logger($aWriter));

        # register router
        $aRouteConfig = $Config->get('system.route');
        $Context->register('Route',
            new Route\Router(
                $aRouteConfig['aBaseConfig'],
                $aRouteConfig['aRule'],
                $sAppNs,
                $aRouteConfig['sBLNS']
            )
        );

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
        $this->Context->register('Request', $HttpRequest);
        $this->Context->register('Response', $HttpResponse);

        # run route
        Context::getInst()->Route
            ->generateFromHttp($HttpRequest, $this->Context->Config->get('system.route.rule'))
            ->call();

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
