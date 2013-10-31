<?php
namespace Slime\Component\Config;

class ConfigureTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $Obj = Configure::factory('@PHP', 'dir_dev', 'dir_pub');
        $this->assertTrue(get_class($Obj)==='Slime\Component\Config\Adaptor_PHP');
    }

    public function testCreateException()
    {
        $sStr = '';
        try {
            $Obj = Configure::factory('\stdClass');
        } catch (\Exception $E) {
            $sStr = $E->getMessage();
        }
        $this->assertEquals('\stdClass must implements Slime\Component\Configure\IAdaptor', $sStr);
    }
}
