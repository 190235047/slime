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
                'PDO->prepare()',
                function (\AopJoinPoint $JoinPoint) {
                    $aArgs = $JoinPoint->getArguments();

                    $Log = Context::getInst()->Log;
                    $Log->debug('SQL : prepare[{query}];', array('query' => $aArgs[0]));
                    $fT1 = microtime(true);
                    call_user_func_array(array($JoinPoint, 'process'), $aArgs);
                    $fT2 = microtime(true);
                    $Log->debug('SQL : cost[{cost}]', array('cost' => sprintf('%.4f', $fT2 - $fT1)));
                }
            );

            \aop_add_around(
                'PDOStatement->execute()',
                function (\AopJoinPoint $JoinPoint) {
                    $aArgs = $JoinPoint->getArguments();

                    $Log = Context::getInst()->Log;
                    $Log->debug('SQL : STMTExec[{param}];', array('param' => $aArgs[0]));
                    $fT1 = microtime(true);
                    call_user_func_array(array($JoinPoint, 'process'), $aArgs);
                    $fT2 = microtime(true);
                    $Log->debug('SQL : cost[{cost}]', array('cost' => sprintf('%.4f', $fT2 - $fT1)));
                }
            );

            \aop_add_around(
                'PDO->exec()',
                function (\AopJoinPoint $JoinPoint) {
                    $aArgs = $JoinPoint->getArguments();

                    $Log = Context::getInst()->Log;
                    $Log->debug('SQL : exec[{query}];', array('query' => $aArgs[0]));
                    $fT1 = microtime(true);
                    call_user_func_array(array($JoinPoint, 'process'), $aArgs);
                    $fT2 = microtime(true);
                    $Log->debug('SQL : cost[{cost}]', array('cost' => sprintf('%.4f', $fT2 - $fT1)));
                }
            );

            \aop_add_around(
                'PDO->query()',
                function (\AopJoinPoint $JoinPoint) {
                    $aArgs = $JoinPoint->getArguments();

                    $Log = Context::getInst()->Log;
                    $Log->debug('SQL : query[{query}];', array('query' => $aArgs[0]));
                    $fT1 = microtime(true);
                    call_user_func_array(array($JoinPoint, 'process'), $aArgs);
                    $fT2 = microtime(true);
                    $Log->debug('SQL : cost[{cost}]', array('cost' => sprintf('%.4f', $fT2 - $fT1)));
                }
            );
        }
    }
}