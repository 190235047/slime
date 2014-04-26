<?php
namespace Slime\Component\RDS;

use Slime\Bundle\Framework\Context;
use Slime\Component\Context\Packer;
use Slime\Component\Log\Logger;

class AopPDO
{
    public static $aAopPreExecCost = array(
        'prepare.after' => array(
            array('Slime\Component\RDS\AopPDO', 'pdoAfter')
        )
    );

    public static function stmtExecBefore($Obj, $sMethod, array $aArgv, \stdClass $Result)
    {
        $Log = Context::getInst()->Log;
        if ($Log->needLog(Logger::LEVEL_INFO)) {
            $fT1   = microtime(true);
            $mRS   = call_user_func_array(array($Obj, $sMethod), $aArgv);
            $fDiff = microtime(true) - $fT1;
            $Log->info(
                '[SQL] : {cost}; {sql}; {bind}; ',
                array(
                    'sql'  => $Obj->queryString,
                    'bind' => empty($aArgv[0]) ? '' : json_encode($aArgv[0]),
                    'cost' => sprintf('%.2f ms', $fDiff * 1000)
                )
            );
        } else {
            $mRS = call_user_func_array(array($Obj, $sMethod), $aArgv);
        }


        $Result->value = $mRS;
    }

    public static function pdoAfter($Obj, $sMethod, array $aArgv, \stdClass $Result)
    {
        $Obj = $Result->value;
        if ($Obj instanceof \PDOStatement) {
            $Result->value = new Packer($Obj,
                array(
                    'execute.before' => array(
                        array('Slime\Component\RDS\AopPDO', 'stmtExecBefore')
                    )
                )
            );
        }
    }
}