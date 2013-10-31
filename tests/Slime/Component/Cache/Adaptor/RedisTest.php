<?php
namespace Slime\Component\Cache;

use Slime\Component\Redis;

class Adaptor_RedisTest extends \PHPUnit_Framework_TestCase
{
    public function testFileGetSetDeleteFlush()
    {
        /** @var \Redis $R */
        $R = new Redis\Redis(
            array(
                'type'     => 'single',
                'pconnect' => true,
                'server'   => array('127.0.0.1', 6379),
            )
        );

        $Cache = Cache::factory('@Redis', $R);
        $Cache->set('a', 'b', 10);
        $this->assertEquals('b', $Cache->get('a'));

        $Cache->delete('a');
        $this->assertFalse($Cache->get('a'));

        $Cache->set('aa', 'bb', 1);
        sleep(2);
        $this->assertFalse($Cache->get('aa'));

        $Cache->set('a1', 'b1', 60);
        $Cache->set('a2', false, 60);
        $this->assertEquals('b1', $Cache->get('a1'));
        $this->assertEquals(false, $Cache->get('a2'));

        $Cache->flush();
        $this->assertFalse($Cache->get('a1'));
        $this->assertFalse($Cache->get('a2'));
    }
}