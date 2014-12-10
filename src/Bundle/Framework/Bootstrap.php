<?php
namespace Slime\Bundle\Framework;

use Slime\Component\Http\REQ;
use Slime\Component\Http\RESP;
use Slime\Component\Route\RouteException;
use Slime\Component\Route\Router;
use Slime\Component\Support\Context;

/**
 * Class Bootstrap
 *
 * @package Slime\Bundle\Framework
 */
class Bootstrap
{
    public static function run(Router $Router, Context $CTX, $nsSAPI = null)
    {
        ($nsSAPI === null ? PHP_SAPI : $nsSAPI) === 'cli' ?
            self::runCli($Router, $CTX):
            self::runHttp($Router, $CTX);
    }

    protected static function runCli(Router $Router, Context $CTX)
    {
        try {
            $CTX->bind('aArgv', $GLOBALS['argv']);
            $Router->runCli($GLOBALS['argv'], $CTX);
        } catch (\Exception $E) {
            $CTX->callIgnore('__Uncaught__', array($E));
            exit(1);
        }
    }

    protected static function runHttp(Router $Router, Context $CTX)
    {
        try {
            $REQ  = REQ::createFromGlobal();
            $RESP = new RESP($REQ->getProtocol());
            $CTX->bindMulti(array('REQ' => $REQ, 'RESP' => $RESP));
            $Router->runHttp($REQ, $RESP, $CTX);
            $RESP->send();
        } catch (RouteException $E) {
            if (isset($RESP)) {
                $RESP->setStatus($E->getCode());
            }
            $CTX->callIgnore('__Uncaught__', array($E));
            exit(1);
        } catch (\Exception $E) {
            $CTX->callIgnore('__Uncaught__', array($E));
            exit(1);
        }
    }
}