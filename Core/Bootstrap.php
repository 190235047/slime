<?php
namespace SlimeFramework\Core;

use SlimeFramework\Component\Config;
use SlimeFramework\Component\Log;
use SlimeFramework\Component\Route;

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
     * @param string $sENV
     * @param string $sDirConfig
     * @param string $sAppNs
     *
     * @return $this
     */
    public static function factory($sENV, $sDirConfig, $sAppNs)
    {
        # get context object from current request
        $Context = Context::getInst();

        # register s_api
        $Context->register('sRT', PHP_SAPI);

        #register env
        $Context->register('sENV', $sENV);

        #register app namespace
        $Context->register('sNS', $sAppNs);

        #register SERVER
        $Context->register('aServer', $_SERVER);

        # register execute datetime object
        $Context->register('DateTime', new \DateTime(date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'])));

        # register config object
        $Config = new Config\Configure($sDirConfig . '/' . $sENV, $sDirConfig . '/' . 'publish');
        $Context->register('Config', $Config);

        # register log object
        $aLogConfig = $Config->get('system.log');
        if (!isset($aLogConfig[PHP_SAPI])) {
            trigger_error('There is no log config to match current runtime:[' . PHP_SAPI . ']', E_USER_ERROR);
            exit(1);
        }
        $aWriter = array();
        foreach ($aLogConfig[PHP_SAPI] as $sWriter => $aArg) {
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

        # register route object
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
        $Context->register('Bootstrap', new self());

        # set error handle
        set_error_handler(array($Context->Bootstrap, 'handleError'));

        return $Context->Bootstrap;
    }

    public function run()
    {
        # run route
        $aRoute = Context::getInst()->Route->run();

        # if logic is an object
        $bCreateObject = !empty($aRoute[2]);

        # pre deal with cb and param
        $CB = null;
        if ($bCreateObject) {
            $RefClass = new \ReflectionClass($aRoute[0][0]);
            $aArr = isset($aRoute[1]) ? (is_array($aRoute[1]) ? $aRoute[1] : array($aRoute[1])) : array();
            $CB = array($RefClass->newInstanceArgs($aArr), array());
            $aParam = array();
        } else {
            $CB = $aRoute[0];
            $aParam = $aRoute[1];
        }

        # call business logic
        if ($bCreateObject && method_exists($CB[0], '__before__')) {
            call_user_func($CB[0], '__before__');
        }
        call_user_func($CB, $aParam);
        if ($bCreateObject && method_exists($CB[0], '__after__')) {
            call_user_func($CB[0], '__after__');
        }
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
