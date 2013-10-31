<?php
namespace Slime\Component\Cache;

use Slime\Component\Memcached;

class Adaptor_MemcachedTest extends \PHPUnit_Framework_TestCase
{
    public function testFileGetSetDeleteFlush()
    {
        /** @var \Memcached $M */
        $M = new Memcached\PHPMemcached(
            array(
                'servers' => array(
                    array('127.0.0.1', 11211, 100)
                )
            )
        );

        $Cache = Cache::factory('@Memcached', $M);
        $Cache->set('a', 'b', 10);
        $this->assertEquals('b', $Cache->get('a'));

        $Cache->delete('a');
        $this->assertFalse($Cache->get('a'));

        $Cache->set('aa', 'bb', 1);
        sleep(2);
        $this->assertFalse($Cache->get('aa'));

        $Cache->set('a1', 'b1', 60);
        $Cache->set('a2', 'b2', 60);
        $this->assertEquals('b1', $Cache->get('a1'));
        $this->assertEquals('b2', $Cache->get('a2'));

        $Cache->flush();

        $this->assertFalse($Cache->get('a1'));
        $this->assertFalse($Cache->get('a2'));
    }
}