<?php
namespace Slime\Component\Cache;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateAdaptorException()
    {
        $sStr = '';
        try {
            new Cache('\\StdClass');
        } catch (\Exception $E) {
            $sStr = $E->getMessage();
        }
        $this->assertEquals("\\StdClass must impl Slime\\Component\\Cache\\IAdaptor", $sStr);
    }
}