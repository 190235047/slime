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
     * @param \Slime\Component\Support\Context $CTX
     * @param array                            $aCTXMap
     * @param mixed                            $mErrorHandle
     * @param mixed                            $mUncaughtExceptionHandle
     */
    public function __construct($CTX, $aCTXMap = array(), $mErrorHandle = null, $mUncaughtExceptionHandle = null)
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
     *
     */
    public function run($Router)
    {
        $CTX = $this->CTX;
        try {
            switch ($CTX->sRunMode) {
                case 'http':
                    $CTX->Route->runHttp($CTX->REQ, $CTX->RESP, $CTX);
                    $CTX->RESP->send();
                    break;
                case 'cli':
                    $CTX->Route->runCli($CTX->aArgv, $CTX);
                    break;
                default:
                    throw new \RuntimeException("[MAIN] : RunMode {$this->CTX->sRunMode} is not supported");
            }
        } catch (RouteException $E) {
            if ($CTX->sRunMode == 'http') {
                $CTX->RESP->setStatus($E->getCode());
            }
            $CTX->call('__UncaughtException__', array($E));
            exit(1);
        } catch (\Exception $E) {
            if ($CTX->isCBBound('__UncaughtException__')) {
                $CTX->call('__UncaughtException__', array($E));
            }
            exit(1);
        }
    }
}