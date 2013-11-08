<?php
namespace Slime\Bundle\Framework;

class AopDebug
{
    public static function register()
    {
        if (function_exists('aop_add_after')) {
            \aop_add_after(
                'Slime\Bundle\Framework\Context->register()',
                function (\AopJoinPoint $JoinPoint) {
                    $Context = Context::getInst();
                    if ($Context===null || !$Context->isRegister('Log')) {
                        return;
                    }
                    $aArgs = $JoinPoint->getArguments();
                    $Context->Log->debug('Context : {name}', array('name' => $aArgs[0]));
                }
            );
        }
    }
}