<?php
namespace Slime\Component\Http;

use Slime\Bundle\Framework\Context;
use Slime\Component\Context\Event;
use Slime\Component\Log\Logger;

/**
 * Class AopPDO
 *
 * @package Slime\Component\RDS
 * @author  smallslime@gmail.com
 */
class Event_Register
{
    const E_CALL_BEFORE = 'Slime.Component.Http.HttpCall.Call:before';
    const E_CALL_AFTER  = 'Slime.Component.Http.HttpCall.Call:after';
    const GV_TIME_PAST  = 'Slime.Component.Http.EventRegister:time';

    public static function register_Call_Before()
    {
        Event::regEvent(
            self::E_CALL_BEFORE,
            function (HttpCall $HC, $sMethodName, $aArgv) {
                Context::getInst()->Arr[self::GV_TIME_PAST] = microtime(true);
            }
        );
    }

    public static function register_Call_After($mResult, HttpCall $HC, $sMethodName, $aArgv)
    {
        Event::regEvent(
            self::E_CALL_AFTER,
            function (HttpCall $HC, $sMethodName, $aArgv) {
                $Log = Context::getInst()->Log;
                if ($Log->needLog(Logger::LEVEL_INFO)) {
                    $Log->info(
                        '[HTTP] : {cost} ; {url}',
                        array(
                            'cost'   => round(microtime(true) - Context::getInst()->Arr[self::GV_TIME_PAST], 4),
                            'method' => $sMethodName,
                            'url'    => $aArgv,
                        )
                    );
                }
            }
        );
    }
}