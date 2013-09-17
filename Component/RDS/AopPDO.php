<?php
namespace SlimeFramework\Component\RDS;

use SlimeFramework\Component\Framework\Context;
use SlimeFramework\Component\Framework\CTX;


/**
 * Class AopPDO
 * @package Slime\RDS
 * @author smallslime@gmail.com
 * @version 0.1
 */
class AopPDO
{
    public static function register()
    {
        aop_add_before(
            "PDOStatement->execute()",
            function (\AopJoinPoint $JoinPoint) {
                /** @var \Slime\Framework\Bootstrap $__Bootstrap__ */
                $aArgs = $JoinPoint->getArguments();
                /** @var \PDOStatement $Object */
                $Object = $JoinPoint->getObject();
                Context::getInst()->Log->debug('SQL : prepare[' . $Object->queryString . '] ; ' . json_encode($aArgs[0]) . '');
            }
        );

        aop_add_before(
            "PDO->exec()",
            function (\AopJoinPoint $JoinPoint) {
                /** @var \Slime\Framework\Bootstrap $__Bootstrap__ */
                $aArgs = $JoinPoint->getArguments();
                Context::getInst()->Log->debug('SQL : exec[' . $aArgs[0] . ']');
            }
        );

        aop_add_before(
            "PDO->query()",
            function (\AopJoinPoint $JoinPoint) {
                /** @var \Slime\Framework\Bootstrap $__Bootstrap__ */
                $aArgs = $JoinPoint->getArguments();
                Context::getInst()->Log->debug('SQL : query[' . $aArgs[0] . ']');
            }
        );
    }
}