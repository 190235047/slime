<?php
namespace Slime\Component\Http;

class HttpRequestTest extends \PHPUnit_Framework_TestCase
{
    public function testGET0()
    {
        $REQ = HttpRequest::create(
            'GET',
            'http://www.google.com/book/view',
            array('id' => 123, 'show' => 1),
            array('Referer' => 'http://www.google.com/'),
            array('uid' => 1, 'last_login' => 123456)
        );

        $this->assertEquals('/book/view?id=123&show=1', $REQ->getRequestURI());
        $REQ->setRequestURI('/abc');
        $this->assertEquals('/abc', $REQ->getRequestURI());

        $this->assertEquals('HTTP/1.1', $REQ->getProtocol());

        $REQ->setProtocol('HTTP/1.0');
        $this->assertEquals('HTTP/1.0', $REQ->getProtocol());

        $this->assertEquals('GET', $REQ->getRequestMethod());
        $REQ->setRequestMethod('POST');
        $this->assertEquals('POST', $REQ->getRequestMethod());

        $this->assertFalse($REQ->isAjax());
        $REQ->setHeader('X_Requested_With', 'xmlhttprequest');
        $this->assertTrue($REQ->isAjax());

        $this->assertNull($REQ->getHeader('X_ABC'));
        $REQ->setHeader(array('X_ABC' => 'def', 'X_DEF' => 'abc'));
        $this->assertEquals($REQ->getHeader('X_ABC'), 'def');
        $this->assertEquals($REQ->getHeader('X_DEF'), 'abc');
        $REQ->setHeader('X_ABC', null);
        $this->assertNull($REQ->getHeader('X_ABC'));
        $REQ->setHeader(array('X_DEF' => null, 'X_OOO' => 'ppp'));
        $this->assertNull($REQ->getHeader('X_DEF'));
        $this->assertEquals($REQ->getHeader('X_OOO'), 'ppp');

        $this->assertNull($REQ->getContent());
        $REQ->setContent('xxxxxxx');
        $this->assertEquals($REQ->getContent(), 'xxxxxxx');
    }

    public function testGET1()
    {
        $REQ = HttpRequest::create(
            'GET',
            '/book/view?u=c',
            array('id' => 123, 'show' => 1),
            array('Referer' => 'http://www.google.com/', 'Host' => 'www.baidu.com'),
            array('uid' => 1, 'last_login' => 123456)
        );

        $this->assertEquals('/book/view?u=c&id=123&show=1', $REQ->getRequestURI());

        $this->assertEquals('123456', $REQ->getCookie('last_login'));
        $this->assertNull($REQ->getCookie('last_login_xxx'));

        $this->assertEquals('http://www.google.com/', $REQ->getHeader('Referer'));

        $this->assertNull($REQ->getContent());

        $this->assertEquals(123, $REQ->getGet('id'));
        $this->assertNull($REQ->getGet('xxx'));
        $this->assertEquals(array('id' => 123, 'show' => 1, 'q' => null), $REQ->getGet(array('id', 'show', 'q')));

        $this->assertEquals(1, $REQ->getGetPost('show'));
        $this->assertEquals(array('id' => 123, 'show' => 1, 'q' => null), $REQ->getGetPost(array('id', 'show', 'q')));
    }

    public function testPOST()
    {
        $REQ = HttpRequest::create(
            'POST',
            '/book/buy',
            array('id' => 123, 'price' => '12.50'),
            array('Referer' => 'http://www.google.com/', 'Host' => 'www.baidu.com'),
            array('uid' => 1, 'last_login' => 123456)
        );

        $this->assertEquals('/book/buy', $REQ->getRequestURI());

        $this->assertEquals('123456', $REQ->getCookie('last_login'));
        $this->assertNull($REQ->getCookie('last_login_xxx'));

        $this->assertEquals('http://www.google.com/', $REQ->getHeader('Referer'));

        $this->assertEquals('id=123&price=12.50', $REQ->getContent());

        $this->assertEquals(123, $REQ->getPost('id'));
        $this->assertNull($REQ->getPost('xxx'));
        $this->assertEquals(array('id' => 123, 'price' => '12.50', 'q' => null), $REQ->getPost(array('id', 'price', 'q')));

        $this->assertEquals('12.50', $REQ->getGetPost('price'));
        $this->assertEquals(array('id' => 123, 'price' => '12.50', 'q' => null), $REQ->getGetPost(array('id', 'price', 'q')));
    }

    public function testGetPOST()
    {
        $REQ = HttpRequest::create(
            'POST',
            '/book/buy',
            array('id' => 123, 'price' => '12.50'),
            array('Referer' => 'http://www.google.com/', 'Host' => 'www.baidu.com'),
            array('uid' => 1, 'last_login' => 123456)
        );
        $REQ->Get['id'] = 456;
        $this->assertEquals(456, $REQ->getGetPost('id'));
        $this->assertEquals(123, $REQ->getGetPost('id', false));

    }

    public function testXSS()
    {
        $REQ = HttpRequest::create(
            'GET',
            '/book/view?u=c',
            array('id' => '<iframe', 'show' => 1),
            array('Referer' => 'http://www.google.com/', 'Host' => 'www.baidu.com'),
            array('uid' => 1, 'last_login' => 123456)
        );

        $this->assertEquals('&lt;iframe', $REQ->getGet('id', true));
        $this->assertEquals('&lt;iframe', $REQ->getGetPost('id', true, true));

        $REQ->preDealXss();
        $this->assertEquals('&lt;iframe', $REQ->getGet('id'));
    }

    public function testCall()
    {
        $REQ = HttpRequest::create('GET', 'http://www.baidu.com');
        $REP = $REQ->call();
        $this->assertEquals($REP->iStatus, 200);
        $this->assertEquals($REP->sProtocol, 'HTTP/1.1');
        $this->assertEquals($REP->sStatusMessage, 'OK');
        $this->assertTrue(count($REP->getContent()) > 0);
    }

    public function testCallPOST()
    {
        $REQ = HttpRequest::create('POST', 'http://www.163.com', array('uid' => 5));
        $REP = $REQ->call();
        $this->assertTrue($REP->iStatus!=200);
    }

    public function testCallFalse()
    {
        $REQ = HttpRequest::create('POST', 'http://www.bai1111xxxxxxxx11111111du.com', array('uid' => 5));
        $REP = $REQ->call();
        $this->assertNull($REP);
    }

    public function testCreateFromGlobals()
    {
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['REQUEST_METHOD'] = 'get';
        $_SERVER['REQUEST_URI'] = '/abc';
        $_SERVER['HTTP_HOST'] = 'www.baidu.com';
        $REQ = HttpRequest::createFromGlobals();

        $this->assertEquals('/abc', $REQ->getRequestURI());
        $this->assertEquals('HTTP/1.1', $REQ->getProtocol());
        $this->assertEquals('GET', $REQ->getRequestMethod());
        $this->assertEquals('www.baidu.com', $REQ->getHeader('Host'));
    }
}