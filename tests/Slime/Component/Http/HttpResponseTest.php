<?php
namespace Slime\Component\Http;

class HttpResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildAndSend()
    {
        $HttpResponse = HttpResponse::create()
            ->setHeader('Host', 'www.baidu.com')->setHeader(array('X-aaa' => 'bbb', 'X-ccc' => 'ddd'))
            ->setNoCache()
            ->setRedirect('http://www.google.com/')
            ->setCookie('k1', 'v1', time()+50, '/')
            ->setContent('contents1');

        $this->assertEquals('contents1', $HttpResponse->getContent());
        $this->assertEquals('http://www.google.com/', $HttpResponse->getHeader('Location'));

        ob_start();
        $HttpResponse->send();
        $sStr = ob_get_contents();
        ob_clean();
        $this->assertEquals('contents1', $sStr);
    }
}