<?php
namespace Slime\Bundle\Framework;

class Test_Context extends \PHPUnit_Framework_TestCase
{
    public function test1()
    {
        Context::makeInst();
        Context::getInst()->register('a', 'test');

        Context::makeInst();
        Context::getInst()->register('a', 'test1');
        Context::getInst()->register('a', 'test11', false);

        Context::makeInst();
        Context::getInst()->register('a', 'test2');
        Context::getInst()->register('a', array('4','5'));

        $this->assertEquals(array('4', '5'), Context::getInst()->a);

        Context::getInst()->destroy();

        $this->assertEquals('test1', Context::getInst()->a);

        Context::getInst()->destroy();
        $this->assertEquals('test', Context::getInst()->a);

        Context::getInst()->destroy();
        $this->assertFalse(Context::getInst());
    }

    public function test2()
    {
        $sStr = '';
        try {
            Context::makeInst();
            Context::getInst()->register('a', 'test');
            Context::getInst()->register('a', 'test2', false, false);
        } catch (\Exception $E) {
            $sStr = $E->getMessage();
        }
        $this->assertEquals("Object register failed. a has exist", $sStr);
    }

    public function test3()
    {
        $sStr = '';
        try {
            Context::makeInst();
            Context::getInst()->register('a', 'test');
            Context::getInst()->b;
        } catch (\Exception $E) {
            $sStr = $E->getMessage();
        }
        $this->assertEquals("Object register failed. b has not exist", $sStr);
    }
}
