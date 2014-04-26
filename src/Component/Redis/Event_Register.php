<?php
namespace Slime\Component\Redis;

use Slime\Bundle\Framework\Context;
use Slime\Component\Context\Event;
use Slime\Component\Log\Logger;

/**
 * Class Event_Register
 *
 * @package Slime\Component\Redis
 * @author  smallslime@gmail.com
 */
class Event_Register
{
    const E_ALL        = 'Slime.Component.Redis.Redis.__All__';
    const GV_TIME_PAST = 'Slime.Component.Redis.EventRegister:time';

    public static function register_All_Before()
    {
        Event::regEvent(
            self::E_ALL,
            function (Redis $Redis, $sMethodName, $aArgv) {
                Context::getInst()->Arr[self::GV_TIME_PAST] = microtime(true);
            }
        );

        Event::regEvent(
            self::E_ALL,
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