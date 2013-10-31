<?php
namespace Slime\Component\DataStructure\Queue;

class QueueTest extends \PHPUnit_Framework_TestCase
{
    public function testAccess()
    {
        $Queue = new Queue();
        $Queue->add(1);
        $this->assertEquals(1, $Queue->current());

        $Queue->add(2);
        $this->assertEquals(1, $Queue->current());

        $Queue->add(3);
        $this->assertEquals(1, $Queue->current());
        $this->assertCount(3, $Queue);

        $this->assertEquals(1, $Queue->get());
        $this->assertEquals(2, $Queue->current());
        $this->assertCount(2, $Queue);

        $this->assertEquals(2, $Queue->get());
        $this->assertEquals(3, $Queue->current());
        $this->assertCount(1, $Queue);

        $this->assertEquals(3, $Queue->get());
        $this->assertNull($Queue->get());
        $this->assertCount(0, $Queue);
    }
}