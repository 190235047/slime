<?php
namespace Slime\Component\Http;

use Slime\Bundle\Framework\Context;
use Slime\Component\Log\Logger;

class AopHttpRequest
{
    public static $aAopHttpCost = array(
        'call.before' => array(
            array('Slime\Component\Http\AopHttpRequest', 'HttpCall')
        )
    );

    public static function HttpCall($Obj, $sMethod, array $aArgv, \stdClass $Result)
    {
        $Log = Context::getInst()->Log;
        if ($Log->needLog(Logger::LEVEL_INFO)) {
            $fT1   = microtime(true);
            $mRS   = call_user_func_array(array($Obj, $sMethod), $aArgv);
            $fDiff = microtime(true) - $fT1;
            $Log->info(
                '[HTTP_CALL] : {cost}; {url};',
                array(
                    'url'  => $aArgv[0],
                    'cost'  => sprintf('%.4f ms', $fDiff * 1000)
                )
            );
        } else {
            $mRS = call_user_func_array(array($Obj, $sMethod), $aArgv);
        }

        $Result->value = $mRS;
    }
}