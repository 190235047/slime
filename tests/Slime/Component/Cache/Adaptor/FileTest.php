<?php
namespace Slime\Component\Cache;

class Adaptor_FileTest extends \PHPUnit_Framework_TestCase
{
    public function testFileGetSetDeleteFlush()
    {
        @rmdir('/tmp/test');
        $Cache = new Cache('@File', '/tmp/test');
        $this->assertNull($Cache->get('key1'));
        $Cache->set('key1', 'value1', 900);
        $this->assertEquals('value1', $Cache->get('key1'));

        $Cache->delete('key1');
        $this->assertNull($Cache->get('key1'));

        $this->assertTrue(file_exists('/tmp/test/cache.php'));
        $Cache->flush();
        $this->assertFalse(file_exists('/tmp/test/cache.php'));

        $this->assertTrue(get_class($Cache->getAdaptor())==='Slime\Component\Cache\Adaptor_File');

        $Cache->set('key1', 'vvv', 3600);
    }

    public function testDelete()
    {
        $Cache = new Cache('@File', '/tmp/test');
        $Cache->delete('key1');
        $this->assertNull($Cache->get('key1'));
    }

    public function testFileExpire()
    {
        $Cache = new Cache('@File', '/tmp/test');
        $Cache->set('key1', 'value1', -1);
        $this->assertEquals(null, $Cache->get('key1'));
    }

    public function testFileCacheFileCallBack()
    {
        $Cache = new Cache('@File', '/tmp/test',
            function($sKey){
                return 'cache_' . (crc32($sKey) % 3) . '.php';
            }
        );
        for ($i=0; $i<100; $i++) {
            $Cache->set('Key' . $i, 'value' . $i, 3600);
        }
        $this->assertEquals('value5', $Cache->get('Key5'));
        $this->assertEquals('value0', $Cache->get('Key0'));
        $this->assertEquals('value30', $Cache->get('Key30'));
        $Cache->flush();
    }

    public function testFileCreateDirException()
    {
        $sStr = '';
        try {
            new Cache('@File', '/bin/createFailed');
        } catch (\Exception $E) {
            $sStr = $E->getMessage();
        }
        $this->assertEquals('Create dir[/bin/createFailed] failed', $sStr);
    }

    public function testFileCreateFileException()
    {
        $Cache = new Cache('@File', '/tmp/test_no_permit', null, 0000);
        $sStr = '';
        try {
            $Cache->set('aaa', 'bbb', 900);
        } catch (\Exception $E) {
            $sStr = $E->getMessage();
        }
        $this->assertEquals('Create file[/tmp/test_no_permit/cache.php] failed', $sStr);
        @rmdir('/tmp/test_no_permit');
    }
}