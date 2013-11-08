<?php
namespace Slime\Bundle\Framework;

class ContextTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateRegisterGetDestroy()
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

        Context::destroy();

        $this->assertEquals('test1', Context::getInst()->a);

        Context::destroy();
        $this->assertEquals('test', Context::getInst()->a);

        Context::destroy();
        $this->assertFalse(Context::getInst());
    }

    public function testRegisterError()
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

    public function testGetError()
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

    public function testIsRegister()
    {
        Context::makeInst();
        Context::getInst()->register('a', 'test');

        $this->assertTrue(Context::getInst()->isRegister('a'));
        $this->assertFalse(Context::getInst()->isRegister('b'));
    }

    public function testClone()
    {
        $sStr = '';
        try {
            Context::makeInst();
            $C = Context::getInst();
            $B = clone $C;
        } catch (\Exception $E) {
            $sStr = $E->getMessage();
        }
        $this->assertEquals('Can not clone', $sStr);
    }
}
