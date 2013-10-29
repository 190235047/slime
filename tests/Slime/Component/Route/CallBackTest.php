<?php
namespace Slime\Component\Route;

class CallBackTest extends \PHPUnit_Framework_TestCase
{
    public function testCallBackFunction1()
    {
        $CallBack = new CallBack('Slime\Component\Route');
        $CallBack->setCBFunc(function(){echo 'hello world!';});
        $this->_doTest($CallBack);
    }

    public function testCallBackFunction2()
    {
        $CallBack = new CallBack('Slime\Component\Route');
        $CallBack->setCBFunc('cbFunction2');
        $CallBack->setParam(array('a' => 'a111', 'b' => 'b222'));
        $this->_doTest($CallBack, 'hello world!a111');
    }

    public function testCallBackClass1()
    {
        $CallBack = new CallBack('Slime\Component\Route');
        $CallBack->setCBClass('TestCB', 'call1');
        $CallBack->setParam(array('a' => 'a1234', 'b'=>'b5678'));
        $this->_doTest($CallBack, 'hello world!a1234');
    }

    public function testCallBackClass2_Error()
    {
        $CallBack = new CallBack('Slime\Component\Route');
        $CallBack->setCBClass('TestCB', 'callXXXX');
        $sMessage = '';
        try {
            $CallBack->call();
        } catch (\RuntimeException $E) {
            $sMessage = $E->getMessage();
        }
        $this->assertEquals('There is no method[callXXXX] in class[Slime\Component\Route\TestCB]', $sMessage);
    }

    public function testCallBackObject1()
    {
        ob_start();
        $CallBack = new CallBack('Slime\Component\Route');
        $CallBack->setCBObject(new TestCB('hey', 'go'), 'call2');
        $CallBack->call();
        $sResult = ob_get_contents();
        ob_end_clean();
        $this->assertEquals('heygohello world!', $sResult);
    }

    public function testCallBackObject2_CreateDelay()
    {
        $CallBack = new CallBack('Slime\Component\Route');
        $CallBack->setCBObject('TestCB', 'call2', array('hey', 'go'));
        $this->_doTest($CallBack, 'heygohello world!');
    }

    public function testCallBackObject2_CreateDelayBeforeAfter()
    {
        $CallBack = new CallBack('Slime\Component\Route');
        $CallBack->setCBObject('TestCB1', 'call');
        $this->_doTest($CallBack, 'before!hello world!after!');
    }

    public function testCallBackObject2_CreateDelayBeforeAfterOverwrite()
    {
        $CallBack = new CallBack('Slime\Component\Route');
        $CallBack->setCBObject('TestCB1', 'call1');
        $this->_doTest($CallBack, 'ow before!hello world!ow after!');
    }

    public function testCallBackObject2_CreateDelayBeforeAfterStopAtBefore()
    {
        $CallBack = new CallBack('Slime\Component\Route');
        $CallBack->setCBObject('TestCB1', 'call2');
        $this->_doTest($CallBack, 'ow2 before!');
    }

    public function testCallBackObject2_CreateDelayBeforeAfterStopAtCall()
    {
        $CallBack = new CallBack('Slime\Component\Route');
        $CallBack->setCBObject('TestCB1', 'call3');
        $this->_doTest($CallBack, 'ow3 before!hello world!');
    }

    private function _doTest(CallBack $CallBack, $sStr = 'hello world!')
    {
        ob_start();
        $CallBack->call();
        $sResult = ob_get_contents();
        ob_end_clean();
        $this->assertEquals($sStr, $sResult);
    }
}

function cbFunction2($aParam)
{
    echo "hello world!" . $aParam['a'];
}

class TestCB
{
    public function __construct($a, $b)
    {
        echo $a . $b;
    }

    public static function call1($aArr = array())
    {
        echo "hello world!";
        if (isset($aArr['a'])) {
            echo $aArr['a'];
        }
    }

    public function call2()
    {
        echo "hello world!";
    }
}

class TestCB1
{
    public function __before__()
    {
        echo "before!";
    }

    public function call()
    {
        echo "hello world!";
    }

    public function __after__()
    {
        echo "after!";
    }

    public function __before_call1__()
    {
        echo "ow before!";
    }

    public function call1()
    {
        echo "hello world!";
    }

    public function __after_call1__()
    {
        echo "ow after!";
    }

    public function __before_call2__()
    {
        echo "ow2 before!";
        return false;
    }

    public function call2()
    {
        echo "hello world!";
    }

    public function __after_call2__()
    {
        echo "ow2 after!";
    }

    public function __before_call3__()
    {
        echo "ow3 before!";
    }

    public function call3()
    {
        echo "hello world!";
        return false;
    }

    public function __after_call3__()
    {
        echo "ow3 after!";
    }
}
