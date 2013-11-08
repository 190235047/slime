<?php
namespace Slime\Component\I18N;

use Slime\Component\Http;

class I18NTest extends \PHPUnit_Framework_TestCase
{
    public function testHttpLoad()
    {
        $HttpRequest = Http\HttpRequest::create(
            'GET', '/', array(),
            array(
                'Host' => 'www.google.com',
                'Accept-Language' => 'zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3'
            )
        );
        $I18N = I18N::createFromHttp(__DIR__ . '/language', $HttpRequest);
        $this->assertEquals('你好世界' ,$I18N->get('string.hi'));
        $this->assertEquals('none hit', $I18N->get('string.none'));
        $this->assertNull($I18N->get('string.nostring'));
    }

    public function testHttpLoadWithCookie()
    {
        $HttpRequest = Http\HttpRequest::create(
            'GET', '/', array(),
            array(
                'Host' => 'www.google.com',
                'Accept-Language' => 'zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3'
            ),
            array('test_language_key' => 'en-xxxx')
        );
        $I18N = I18N::createFromHttp(__DIR__ . '/language', $HttpRequest, 'zh-cn', 'test_language_key');
        $this->assertEquals('hello world' ,$I18N->get('string.hi'));
    }

    public function testCli()
    {
        $aArg = array('1.php', 'xxx.aaa', '{}', 'zh-cn');
        $I18N = I18N::createFromCli(__DIR__ . '/language', $aArg, 'english');
        $this->assertEquals('你好世界' ,$I18N->get('string.hi'));
    }

    public function testCliNoHit()
    {
        $aArg = array('1.php', 'xxx.aaa', '{}', 'zhxxxx-cn');
        $I18N = I18N::createFromCli(__DIR__ . '/language', $aArg, 'english');
        $this->assertEquals('hello world' ,$I18N->get('string.hi'));
    }
}