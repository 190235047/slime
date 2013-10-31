<?php
namespace Slime\Component\Config;

class Adaptor_PHPTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadAndGetDev()
    {
        $sDevConfDir = dirname(__DIR__) . '/config_dev/';
        $sPubConfDir = dirname(__DIR__) . '/config_pub/';

        $Config = Configure::factory('@PHP', $sDevConfDir, $sPubConfDir);
        $this->assertEquals('dev_v1', $Config->get('system.key1'));
    }

    public function testLoadAndGetPub()
    {
        $sDevConfDir = dirname(__DIR__) . '/config_dev/';
        $sPubConfDir = dirname(__DIR__) . '/config_pub/';

        $Config = Configure::factory('@PHP', $sPubConfDir, $sPubConfDir);
        $this->assertEquals('pub_v1', $Config->get('system.key1'));
    }

    public function testGetException()
    {
        $sDevConfDir = dirname(__DIR__) . '/config_dev/';
        $sPubConfDir = dirname(__DIR__) . '/config_pub/';

        $Config = Configure::factory('@PHP', $sDevConfDir, $sPubConfDir);
        $this->assertNull($Config->get('system.not_exist_key', null, false));
        $sStr = '';
        try {
            $Config->get($Config->get('system.not_exist_key', null, true));
        } catch (\Exception $E) {
            $sStr = $E->getMessage();
        }
        $this->assertEquals("Config [system.not_exist_key] is not found", $sStr);
    }

    public function testGetWithDir()
    {
        $sDevConfDir = dirname(__DIR__) . '/config_dev/';
        $sPubConfDir = dirname(__DIR__) . '/config_pub/';

        $Config = Configure::factory('@PHP', $sDevConfDir, $sPubConfDir);
        $this->assertEquals(10, $Config->get('tv:tvb.author.man'));
    }

    public function testGetFileAll()
    {
        $sDevConfDir = dirname(__DIR__) . '/config_dev/';
        $sPubConfDir = dirname(__DIR__) . '/config_pub/';

        $Config = Configure::factory('@PHP', $sDevConfDir, $sPubConfDir);
        $this->assertEquals(array('key1' => 'dev_v1'), $Config->get('system'));
    }
}