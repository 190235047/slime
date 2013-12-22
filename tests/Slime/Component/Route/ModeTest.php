<?php
namespace Slime\Component\Route;

use Slime\Component\Http;

class ModeTest extends \PHPUnit_Framework_TestCase
{
    public function __construct()
    {
        parent::__construct();
        $this->HttpResponse = Http\HttpResponse::create()->setNoCache();
        $this->Router = new Router('Slime\Component\Route');
    }

    public function testModeSlimeHttp()
    {
        $HttpRequest = Http\HttpRequest::create('GET', '/', array(), array('Host' => 'www.google.com'));
        $aCallBack = $this->Router->generateFromHttp($HttpRequest, $this->HttpResponse,
            array(
                array('Slime\Component\Route\Mode', 'slimeHttp')
            )
        );

        $this->assertCount(1, $aCallBack);
        $this->assertEquals(array('Slime\Component\Route\ControllerHttp_Main', 'actionDefault'), $aCallBack[0]->mCallable);
    }

    public function testModeSlimeHttp1()
    {
        $HttpRequest = Http\HttpRequest::create('POST', '/post/Hk.html', array(), array('Host' => 'www.google.com'));
        $aCallBack = $this->Router->generateFromHttp($HttpRequest, $this->HttpResponse,
            array(
                array('Slime\Component\Route\Mode', 'slimeHttp')
            )
        );

        $this->assertCount(1, $aCallBack);
        $this->assertEquals(array('Slime\Component\Route\ControllerHttp_Post', 'actionHk_POST'), $aCallBack[0]->mCallable);
    }

    public function testModeSlimeHttp2()
    {
        $HttpRequest = Http\HttpRequest::create('GET', '/tv/tvb/hk', array(), array('Host' => 'www.google.com'));
        $aCallBack = $this->Router->generateFromHttp($HttpRequest, $this->HttpResponse,
            array(
                array('Slime\Component\Route\Mode', 'slimeHttp')
            )
        );

        $this->assertCount(1, $aCallBack);
        $this->assertEquals(array('Slime\Component\Route\ControllerHttp_Tv_Tvb', 'actionHk'), $aCallBack[0]->mCallable);
    }

    public function testModeSlimeCli()
    {
        ob_start();
        $aCallBack = $this->Router->generateFromCli(
            array('index.php', 'Call1.Go', '{"xxx":"zzz", "xx":["a","b","c"]}'),
            array(
                function($aArg, &$bContinue, $sAppNs) {
                    echo 'hello world!';
                    $bContinue = true;
                },
                array('Slime\Component\Route\Mode', 'slimeCli')
            )
        );
        $sStr = ob_get_contents();
        ob_end_clean();
        $this->assertEquals('hello world!', $sStr);
        $this->assertEquals(1, count($aCallBack));
        $this->assertEquals(array('Slime\Component\Route\ControllerCli_Call1', 'actionGo'), $aCallBack[0]->mCallable);
        $this->assertEquals(array('xxx'=>'zzz', 'xx' => array('a', 'b', 'c')), $aCallBack[0]->aObjInitParam[0]);
    }

    public function testModeSlimeCli1()
    {
        ob_start();
        $aCallBack = $this->Router->generateFromCli(
            array('index.php', 'Call1', '{"xxx":"zzz", "xx":["a","b","c"]}'),
            array(
                function($aArg, &$bContinue, $sAppNs) {
                    echo 'hello world!';
                    $bContinue = true;
                },
                array('Slime\Component\Route\Mode', 'slimeCli')
            )
        );
        $sStr = ob_get_contents();
        ob_end_clean();
        $this->assertEquals('hello world!', $sStr);
        $this->assertEquals(1, count($aCallBack));
        $this->assertEquals(array('Slime\Component\Route\ControllerCli_Call1', 'actionDefault'), $aCallBack[0]->mCallable);
        $this->assertEquals(array('xxx'=>'zzz', 'xx' => array('a', 'b', 'c')), $aCallBack[0]->aObjInitParam[0]);
    }
}

