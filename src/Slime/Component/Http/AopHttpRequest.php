<?php
namespace Slime\Component\Http;

use Slime\Bundle\Framework\Context;
use Slime\Component\Log\Logger;

class AopRedis
{
    public static function HttpREQ($Obj, $sMethod, array $aArgv, \stdClass $Result)
    {
        $Log = Context::getInst()->Log;
        if ($Log->needLog(Logger::LEVEL_INFO)) {
            $fT1   = microtime(true);
            $mRS   = call_user_func_array(array($Obj, $sMethod), $aArgv);
            $fDiff = microtime(true) - $fT1;
            $Log->info(
                '[HTTP_REQ] : {cost}; {info}; {param}',
                array(
                    'info'  => (string)$Obj,
                    'param' => json_encode($aArgv),
                    'cost'  => sprintf('%.4f ms', $fDiff * 1000)
                )
            );
        } else {
            $mRS = call_user_func_array(array($Obj, $sMethod), $aArgv);
        }

        $Result->value = $mRS;
    }

    public static function getAopConf()
    {
        return array(
            '/.*/.before' => array(
                array('Slime\Component\Http\HttpREQ', 'HttpREQ')
            )
        );
    }
}