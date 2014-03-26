<?php
namespace Slime\Component\Memcached;

use Slime\Bundle\Framework\Context;
use Slime\Component\Log\Logger;

class AopMemcached
{
    public static $aAopAllCMDCost = array(
        '/.*/.before' => array(
            array('Slime\Component\Memcached\AopRedis', 'MemcachedCMD')
        )
    );

    public static function MemcachedCMD($Obj, $sMethod, array $aArgv, \stdClass $Result)
    {
        $Log   = Context::getInst()->Log;
        if ($Log->needLog(Logger::LEVEL_INFO)) {
            $fT1   = microtime(true);
            $mRS   = call_user_func_array(array($Obj, $sMethod), $aArgv);
            $fDiff = microtime(true) - $fT1;
            $Log->info(
                '[REDIS] : {cost}; {cmd}; {param}; ',
                array(
                    'cmd'   => $sMethod,
                    'param' => json_encode($aArgv),
                    'cost'  => sprintf('%.4f ms', $fDiff * 1000)
                )
            );
        } else {
            $mRS = call_user_func_array(array($Obj, $sMethod), $aArgv);;
        }

        $Result->value = $mRS;
    }
}