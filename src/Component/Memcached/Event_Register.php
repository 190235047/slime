<?php
namespace Slime\Component\Memcached;

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
    const E_ALL_BEFORE = 'Slime.Component.Memcached.PHPMemcached.__All__:before';
    const E_ALL_AFTER  = 'Slime.Component.Memcached.PHPMemcached.__All__:after';
    const GV_TIME_PAST = 'Slime.Component.Memcached.EventRegister:time';

    public static function register_All_Before()
    {
        Event::regEvent(
            self::E_ALL_BEFORE,
            function (PHPMemcached $MC, $sMethodName, $aArgv) {
                Context::getInst()->Arr[self::GV_TIME_PAST] = microtime(true);
            }
        );
    }

    public static function register_All_After()
    {
        Event::regEvent(
            self::E_ALL_AFTER,
            function ($mResult, PHPMemcached $MC, $sMethodName, $aArgv) {
                $Log = Context::getInst()->Log;
                if ($Log->needLog(Logger::LEVEL_INFO)) {
                    $Log->info(
                        '[MC] : {cost} ; {cmd}',
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