<?php
namespace Slime\Component\Redis;

class RedisTest extends \PHPUnit_Framework_TestCase
{
    public function testRedis()
    {
        /** @var \Redis $Redis */
        $Redis = new Redis(
            array(
                'type'     => 'single',
                'pconnect' => true,
                'server'   => array('127.0.0.1', 6379),
                'options'  => array(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP),
            )
        );

        $Redis->set('a', array(1,2,3));
        $this->assertEquals(array(1,2,3), $Redis->get('a'));

        $Redis->delete('a');
        $this->assertFalse($Redis->get('a'));
    }

    public function testRedisArray()
    {
        /** @var \RedisArray $Redis */
        $Redis = new Redis(
            array(
                'type'     => 'multi',
                'pconnect' => true,
                'servers'  => array('127.0.0.1:6379', '127.0.0.1:6380')
            )
        );

        $Redis->set('a', json_encode(array(1,2,3)));
        $this->assertEquals(array(1,2,3), json_decode($Redis->get('a')));

        $Redis->set('b', json_encode(array(1,2,3)));
        $this->assertEquals(array(1,2,3), json_decode($Redis->get('b')));

        $Redis->delete('a');
        $this->assertFalse($Redis->get('a'));
    }
}