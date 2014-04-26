<?php
namespace Slime\Component\Redis;

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
    const E_ALL_BEFORE = 'Slime.Component.Redis.Redis.__All__:before';
    const E_ALL_AFTER  = 'Slime.Component.Redis.Redis.__All__:after';
    const GV_TIME_PAST = 'Slime.Component.Redis.EventRegister:time';

    public static function register_All_Before()
    {
        Event::regEvent(
            self::E_ALL_BEFORE,
            function (Redis $Redis, $sMethodName, $aArgv) {
                Context::getInst()->Arr[self::GV_TIME_PAST] = microtime(true);
            }
        );
    }

    public static function register_All_After()
    {
        Event::regEvent(
            self::E_ALL_AFTER,
            function ($mResult, Redis $Redis, $sMethodName, $aArgv) {
                $Log = Context::getInst()->Log;
                if ($Log->needLog(Logger::LEVEL_INFO)) {
                    $Log->info(
                        '[REDIS] : {cost} ; {cmd}',
                        array(
                            'cost' => microtime(true) - Context::getInst()->Arr[self::GV_TIME_PAST],
                            'cmd'  => $sMethodName,
                        )
                    );
                }
            }
        );
    }
}