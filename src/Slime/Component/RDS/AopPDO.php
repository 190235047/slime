<?php
namespace Slime\Component\RDS;

use Slime\Bundle\Framework\Context;
use Slime\Component\Helper\Packer;

class AopPDO
{
    public static function stmtExecBefore($Obj, $sMethod, array $aArgv, \ArrayObject $Result)
    {
        $fT1 = microtime(true);
        $mRS = call_user_func_array(array($Obj, $sMethod), $aArgv);
        $fDiff = microtime(true) - $fT1;
        Context::getInst()->Log->info(
            'SQL : {cost}; {sql}; {bind}; ',
            array(
                'sql'  => $Obj->queryString,
                'bind' => empty($aArgv[0]) ? '' : json_encode($aArgv[0]),
                'cost' => sprintf('%.2f ms', $fDiff * 1000)
            )
        );

        $Result['value'] = $mRS;
        return false;
    }

    public static function pdoAfter($Obj, $sMethod, array $aArgv, \ArrayObject $Result)
    {
        $Obj = $Result['value'];
        if ($Obj instanceof \PDOStatement) {
            $Result['value'] = new Packer($Obj,
                array(
                    'execute.before' => array(
                        array('Slime\Component\RDS\AopPDO', 'stmtExecBefore')
                    )
                )
            );
        }
    }

    public static function getAopConf()
    {
        return array(
            'prepare.after' => array(
                array('Slime\Component\RDS\AopPDO', 'pdoAfter')
            )
        );
    }
}