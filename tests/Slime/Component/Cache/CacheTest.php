<?php
namespace Slime\Component\Cache;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateAdaptorException()
    {
        $sStr = '';
        try {
            Cache::factory('\\StdClass');
        } catch (\Exception $E) {
            $sStr = $E->getMessage();
        }
        $this->assertEquals("\\StdClass must implements Slime\\Component\\Cache\\IAdaptor", $sStr);
    }
}