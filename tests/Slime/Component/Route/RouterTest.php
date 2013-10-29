<?php
namespace Slime\Component\Route;

use Slime\Component\Http;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    public function __construct()
    {
        parent::__construct();
        $this->HttpRequest = Http\HttpRequest::create('GET', '/tv/tvb/hk', array(), array('Host' => 'www.google.com'));
        $this->HttpResponse = Http\HttpResPonse::create()->setNoCache();
        $this->Router = new Router('Slime\Component\Route');
    }

    public function testHttpCallAble1()
    {
        $this->Router = new Router('Slime\Component\Route'); // for code coverage
        $aCallBack = $this->Router->generateFromHttp($this->HttpRequest, $this->HttpResponse,
            array(
                function(Http\HttpRequest $Req, Http\HttpResponse $Rep, &$bContinue, $sAppNs){
                    $Rep->setContent('hello world!');
                },
                array('Slime\Component\Route\Mode', 'slimeHttp')
            )
        );
        $this->assertEquals('hello world!', $this->HttpResponse->getContent());
        $this->assertEmpty($aCallBack);
    }

    public function testHttpCallAble2()
    {
        $aCallBack = $this->Router->generateFromHttp($this->HttpRequest, $this->HttpResponse,
            array(
                function(Http\HttpRequest $Req, Http\HttpResponse $Rep, &$bContinue, $sAppNs){
                    $Rep->setContent('hello world!');
                    $bContinue = true;
                },
                array('Slime\Component\Route\Mode', 'slimeHttp')
            )
        );
        $this->assertEquals('hello world!', $this->HttpResponse->getContent());
        $this->assertEquals(1, count($aCallBack));
        $this->assertEquals(array('Slime\Component\Route\ControllerHttp_Tv_Tvb', 'actionHk'), $aCallBack[0]->mCallable);
    }

    public function testHttpCallAble3()
    {
        $aCallBack = $this->Router->generateFromHttp($this->HttpRequest, $this->HttpResponse,
            array(
                '#^/abc/def#' => function(Http\HttpRequest $Req, Http\HttpResponse $Rep, $aParam, &$bContinue, $sAppNs){
                    $Rep->setHeader('Xxx', 'YYY');
                },
                '#^/tv/(.*?)/([^/]*)$#' => function(Http\HttpRequest $Req, Http\HttpResponse $Rep, $aParam, &$bContinue, $sAppNs){
                    $Rep->setContent("1:{$aParam[1]};2:{$aParam[2]};");
                    $bContinue = true;
                },
                '#^/(.*?)/(.*?)/[^/]*$#' => function(Http\HttpRequest $Req, Http\HttpResponse $Rep, $aParam, &$bContinue, $sAppNs){
                    $Rep->setHeader('Xxx', 'ZZZ');
                },
                array('Slime\Component\Route\Mode', 'slimeHttp')
            )
        );
        $this->assertEquals('1:tvb;2:hk;', $this->HttpResponse->getContent());
        $this->assertEquals('ZZZ', $this->HttpResponse->getHeader('Xxx'));
        $this->assertEmpty($aCallBack);
    }

    public function testHttpCallArray()
    {
        $aCallBack = $this->Router->generateFromHttp($this->HttpRequest, $this->HttpResponse,
            array(
                '#^/tv/(.*?)/([^/]*)$#' => array('_continue' => true, 'object' => 'TestCBClass', 'method' => 'run'),
                '#^/(.*?)/tvb/([^/]*)$#' => array('_continue' => true, 'func' => 'call_$1', 'param' => array('a' => 't1', 'b' => 't2:$1_$2')),
                '#^/tv/tvb/([^/]*)$#' => array('_continue' => true, 'class' => 'call_$1', 'method' => 'run'),
                '#^/(.*?)/(.*?)/[^/]*$#' => function(Http\HttpRequest $Req, Http\HttpResponse $Rep, $aParam, &$bContinue, $sAppNs){
                    $Rep->setHeader('Xxx', 'ZZZ');
                    $bContinue = true;
                },
                array('Slime\Component\Route\Mode', 'slimeHttp')
            )
        );
        $this->assertEquals('ZZZ', $this->HttpResponse->getHeader('Xxx'));
        $this->assertEquals(4, count($aCallBack));

        $this->assertEquals(array('Slime\Component\Route\TestCBClass', 'run'), $aCallBack[0]->mCallable);

        $this->assertEquals('Slime\Component\Route\call_tv', $aCallBack[1]->mCallable);
        $this->assertEquals(array('a' => 't1', 'b' => 't2:tv_hk'), $aCallBack[1]->aParam);

        $this->assertEquals(array('Slime\Component\Route\call_hk', 'run'), $aCallBack[2]->mCallable);

        $this->assertEquals(array('Slime\Component\Route\ControllerHttp_Tv_Tvb', 'actionHk'), $aCallBack[3]->mCallable);
    }

    public function testHttpCallAbleError()
    {
        $sStr = '';
        try {
            $aCallBack = $this->Router->generateFromHttp($this->HttpRequest, $this->HttpResponse,
                array(
                    '#^/tv/(.*?)/([^/]*)$#' => array('_continue' => true),
                )
            );
        } catch (\Exception $E) {
            $sStr = $E->getMessage();
        }
        $this->assertEquals('Route rule error. one of [object, class, func] must be used for array key', $sStr);
    }

    public function testHttpCallAble5()
    {
        $aCallBack = $this->Router->generateFromHttp($this->HttpRequest, $this->HttpResponse,
            array(
                '#^/(.*?)/(.*?)/[^/]*$#' => function(Http\HttpRequest $Req, Http\HttpResponse $Rep, $aParam, &$bContinue, $sAppNs){
                    $CallBack = new CallBack($sAppNs);
                    $CallBack->setCBFunc('runCB');
                    $bContinue = true;
                    return $CallBack;
                },
                '#^/tv/(.*?)/[^/]*$#' => function(Http\HttpRequest $Req, Http\HttpResponse $Rep, $aParam, &$bContinue, $sAppNs){
                    $CallBack = new CallBack($sAppNs);
                    $CallBack->setCBClass('runCbTv', 'run');
                    $bContinue = true;
                    return $CallBack;
                },
            )
        );
        $this->assertEquals(2, count($aCallBack));
        $this->assertEquals('Slime\Component\Route\runCB', $aCallBack[0]->mCallable);
        $this->assertEquals(array('Slime\Component\Route\runCbTv', 'run'), $aCallBack[1]->mCallable);
    }

    public function testCliCallAble1()
    {
        ob_start();
        $aCallBack = $this->Router->generateFromCli(
            array('index.php', 'Call1.Method1', '{"xxx":"zzz", "xx":["a","b","c"]}'),
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
        $this->assertEquals(array('Slime\Component\Route\ControllerCli_Call1', 'actionMethod1'), $aCallBack[0]->mCallable);
        $this->assertEquals(array('xxx'=>'zzz', 'xx' => array('a', 'b', 'c')), $aCallBack[0]->aObjInitParam[0]);
    }
}
