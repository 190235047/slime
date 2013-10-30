<?php
namespace Slime\Component\View;

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
        \aop_add_before(
            'Slime\Component\View\Adaptor_PHP->renderAsResult()',
            function (\AopJoinPoint $JoinPoint) {
                //$aArgs = $JoinPoint->getArguments();
                $Class = $JoinPoint->getObject();

                $Log = Context::getInst()->Log;
                $Log->debug('TPL : {path}', array('path' => $Class->getBaseDir() . DIRECTORY_SEPARATOR . $Class->getTpl()));
            }
        );
    }
}