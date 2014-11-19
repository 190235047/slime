<?php
namespace Slime\Bundle\Framework;

use Slime\Component\Http\REQ;
use Slime\Component\Http\RESP;
use Slime\Component\Route\RouteException;
use Slime\Component\Route\Router;

/**
 * Class Bootstrap
 *
 * @package Slime\Bundle\Framework
 */
class Bootstrap
{
    /**
     * @var \Slime\Bundle\Framework\Context
     */
    protected $CTX;

    /**
     * @param \Slime\Component\Config\IAdaptor $CFG
     * @param \Slime\Component\Support\Context $CTX
     * @param array                            $aCTXMap
     * @param mixed                            $mErrorHandle
     * @param mixed                            $mUncaughtExceptionHandle
     */
    public function __construct($CFG, $CTX, $aCTXMap = array(), $mErrorHandle = null, $mUncaughtExceptionHandle = null)
    {
        $this->CTX = $CTX;

        $CTX->bindCB(
            '__UncaughtException__',
            $mUncaughtExceptionHandle === null ?
                array('\\Slime\\Bundle\\Framework\\Ext', 'handleUncaughtException') :
                $mUncaughtExceptionHandle
        );

        set_error_handler(
            $mErrorHandle === null ?
                array('\\Slime\\Bundle\\Framework\\Ext', 'handleError') :
                $mErrorHandle,
            E_ALL | E_STRICT
        );

        if (!isset($aCTXMap['sRunMode'])) {
            $aCTXMap['sRunMode'] = strtolower(PHP_SAPI) === 'cli' ? 'cli' : 'http';
        }
        if (!isset($aCTXMap['Bootstrap'])) {
            $aCTXMap['Bootstrap'] = $this;
        }
        if (!isset($aCTXMap['Config'])) {
            $aCTXMap['Config'] = $CFG;
        }
        if (!isset($aCTXMap['Route'])) {
            $aCTXMap['Route'] = new Router();
        }
        if ($aCTXMap['sRunMode'] === 'cli') {
            if (!isset($aCTXMap['aArgv'])) {
                $aMap['aArgv'] = $GLOBALS['argv'];
            }
        } else {
            if (!isset($aCTXMap['REQ'])) {
                $aCTXMap['REQ'] = REQ::createFromGlobal();
            }
            if (!isset($aCTXMap['RESP'])) {
                $aCTXMap['RESP'] = new RESP();
            }
        }

        $CTX->bindMulti($aCTXMap);
    }

    /**
     * @param array                                $aRouteConfig
     * @param \Slime\Component\Log\LoggerInterface $Log
     */
    public function run($aRouteConfig, $Log)
    {
        $fT1 = microtime(true);
        $CTX = $this->CTX;
        try {
            switch ($CTX->sRunMode) {
                case 'http':
                    $REQ  = $CTX->REQ;
                    $RESP = $CTX->RESP;
                    $Log->info(sprintf('RUN_START ; %s ; %s', $REQ->getRequestMethod(), $REQ->getRequestURI()));
                    $CTX->Route->setMulti($aRouteConfig)->run($REQ, $RESP, $Log, $CTX);
                    $RESP->send();
                    break;
                case 'cli':
                    $Log->info("[MAIN] ; start ; " . json_encode($CTX->aArgv));
                    $CTX->Route->setMulti($aRouteConfig)->run($CTX->aArgv, null, $Log, $CTX);
                    break;
                default:
                    throw new \RuntimeException("[MAIN] : RunMode {$this->CTX->sRunMode} is not supported");
            }
        } catch (RouteException $E) {
            if ($CTX->sRunMode == 'http') {
                $CTX->RESP->setResponseCode($E->getCode());
            }
            $CTX->call('__UncaughtException__', array($E));
            $Log->info('RUN_END : ' . round(microtime(true) - $fT1, 6));
            exit(1);
        } catch (\Exception $E) {
            if ($CTX->isCBBound('__UncaughtException__')) {
                $CTX->call('__UncaughtException__', array($E));
            }
            $Log->info('RUN_END : ' . round(microtime(true) - $fT1, 6));
            exit(1);
        }
        $Log->info('RUN_END : ' . round(microtime(true) - $fT1, 6));
    }
}