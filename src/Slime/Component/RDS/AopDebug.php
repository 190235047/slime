<?php
namespace Slime\Component\RDS;

use Slime\Bundle\Framework\Context;


/**
 * Class AopPDO
 *
 * @package Slime\Component\RDS
 * @author  smallslime@gmail.com
 */
class AopDebug
{
    public static function register()
    {
        if (function_exists('aop_add_around')) {
            \aop_add_around(
                'PDOStatement->execute()',
                function (\AopJoinPoint $JoinPoint) {
                    $aArgs = $JoinPoint->getArguments();
                    /** @var \PDOStatement $STMT */
                    $STMT = $JoinPoint->getObject();

                    $Log = Context::getInst()->Log;
                    $Log->debug('SQL : {sql};', array('sql' => $STMT->queryString));
                    if (!empty($aArgs[0])) {
                        $Log->debug('SQL : bind {bind};', array('bind' => $aArgs[0]));
                    }
                    $fT1 = microtime(true);
                    call_user_func_array(array($JoinPoint, 'process'), $aArgs);
                    $fT2 = microtime(true);
                    $Log->debug('SQL : cost[{cost}]', array('cost' => sprintf('%.4f', $fT2 - $fT1)));
                }
            );
        }
    }
}