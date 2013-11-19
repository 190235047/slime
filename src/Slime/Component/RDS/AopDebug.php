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

                    $fT1 = microtime(true);
                    call_user_func_array(array($JoinPoint, 'process'), $aArgs);
                    $fT2 = microtime(true);

                    $Log = Context::getInst()->Log;
                    $Log->debug(
                        'SQL : {cost}; {sql}; {bind}; ',
                        array(
                            'sql'  => $STMT->queryString,
                            'bind' => empty($aArgs[0]) ? '' : $aArgs[0],
                            'cost' => sprintf('%.2f ms', ($fT2 - $fT1) * 1000)
                        )
                    );
                }
            );
        }
    }
}