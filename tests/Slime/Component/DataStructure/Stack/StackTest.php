<?php
namespace Slime\Component\DataStructure\Stack;

class StackTest extends \PHPUnit_Framework_TestCase
{
    public function testPoshPopGetCount()
    {
        $Stack = new Stack();
        $Stack->push('aaa');
        $this->assertCount(1, $Stack);
        $this->assertEquals('aaa', $Stack->current());
        $Stack->push('bbb');
        $this->assertCount(2, $Stack);
        $this->assertEquals('bbb', $Stack->current());
        $b = $Stack->pop();
        $this->assertCount(1, $Stack);
        $this->assertEquals('bbb', $b);
        $this->assertEquals('aaa', $Stack->current());
    }
}