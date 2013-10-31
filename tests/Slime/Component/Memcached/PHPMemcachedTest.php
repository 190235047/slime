<?php
namespace Slime\Component\Memcached;

class PHPMemcachedTest extends \PHPUnit_Framework_TestCase
{
    public function testCall()
    {
        /** @var \Memcached $M */
        $M = new PHPMemcached(
            array(
                'servers' => array(
                    array('127.0.0.1', 11211, 100)
                )
            )
        );

        $M->set('key1', 'value1', 60);
        $this->assertEquals('value1', $M->get('key1'));
    }
}