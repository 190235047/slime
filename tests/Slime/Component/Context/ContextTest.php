<?php
namespace Slime\Component\Context;

use Slime\Component\DataStructure\Stack;

class ContextTest extends \PHPUnit_Framework_TestCase
{
    public function testAAA()
    {
        Context::makeInst();
        $Context = Context::getInst();
        $Context->aInject = array(
            'Slime\Component\Context\A' => array(
                'getA' => array(
                    'before' => array(
                        function($Obj, DataContent $Method, DataContent $Arg)
                        {
                            $Obj->a = 'before_' . $Obj->a;
                        }
                    ),
                    'after' => array(
                        function($Obj, DataContent $Method, DataContent $Arg, DataContent $Ret)
                        {
                            $Obj->a     = $Obj->a . '_after';
                            $Ret->mData = $Obj->a;
                        }
                    ),
                    'replace' =>
                        function($Obj, $sMethod, $aArg)
                        {
                            $T1 = microtime(true);
                            $mResult = call_user_func_array(array($Obj, $sMethod), $aArg);
                            usleep(10000);
                            $T2 = microtime(true);
                            var_dump(round($T2 - $T1, 6));
                            return $mResult;
                        }
                )
            )
        );
        $Context->registerObjWithArgsAndContent('A', 'Slime\Component\Context\A', array('abc', 'def', 'ghi'));
        $A = $Context->A;
        var_dump($A->getA());
    }
}

class A
{
    public function __construct($a, $b, $c = 123)
    {
        $this->a = $a;
        $this->b = $b;
        $this->c = $c;
    }

    public function getA()
    {
        return $this->a;
    }
}