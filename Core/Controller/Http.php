<?php
namespace SlimeFramework\Core;

use SlimeFramework\Component\Http;
use SlimeFramework\Component\Route\CallBack;
use SlimeFramework\Component\View\Viewer;

/**
 * Class Controller_Http
 * SlimeFramework 内置Http控制器基类
 *
 * @package SlimeFramework\Core
 * @author  smallslime@gmail.com
 */
abstract class Controller_Http
{
    protected $Context;

    protected $sTPL = null;
    protected $aData = array();

    /**
     * 本次 REQUEST_METHOD 是否为 GET
     *
     * @var bool
     */
    protected $bGet;

    /**
     * 本次请求是否为 AJAX
     *
     * @var bool
     */
    protected $bAjax;

    /**
     * 是否自动渲染页面
     *     true  : 业务逻辑完成后, 自动加载模板, 渲染页面;
     *             模板名默认为类名去掉前面的 NAMESPACE\ControllerHttp_
     *             可以通过继承 override getDefaultTPL 方法重写取默认模板逻辑
     *     false : 不自动渲染页面
     * 变量在构造函数中初始化, 若请求为GET, 则为true, 否则为false
     *
     * @var bool
     */
    protected $bAutoRender;

    /**
     * 是否自动重定向
     *     true     : 业务逻辑完成后, 重定向 REFERER页 或 /
     *     false    : 不做任何重定向逻辑
     * 变量在构造函数中初始化, 若请求不为GET且不为Ajax, 则为true, 否则为false
     *
     * @var bool
     */
    protected $bAutoRedirect;

    public function __construct(array $aParam = array())
    {
        $this->Context       = $Context = Context::getInst();
        $this->Log           = $Context->Log;
        $this->Config        = $Context->Config;
        $this->HttpRequest   = $Context->HttpRequest;
        $this->HttpResponse  = $Context->HttpResponse;
        $this->aParam        = $aParam;
        $this->View          = Viewer::factory('@PHP', $this->Log)->setBaseDir(DIR_VIEW);
        $this->bGet          = $this->HttpRequest->getRequestMethod() === 'GET';
        $this->bAutoRender   = $this->bGet;
        $this->bAjax         = $this->HttpRequest->isAjax();
        $this->bAutoRedirect = (!$this->bGet) && (!$this->bAjax);
    }

    /**
     * 主逻辑完成后运行
     */
    public function __after__()
    {
        if (!empty($this->aParam['__INNER_CALL__'])) {
            return;
        }
        # header
        if ($this->HttpResponse->getHeader('Content-Type') === null) {
            if ($this->bAjax) {
                $this->HttpResponse->setHeader('Content-Type', 'application/javascript; charset=utf-8', false);
            } else {
                $this->HttpResponse->setHeader('Content-type', 'text/html; charset=utf-8', false);
            }
        }
        if ($this->bAutoRedirect && $this->HttpResponse->getHeader('Location') === null) {
            $this->HttpResponse->setRedirect($this->HttpRequest->getHeader('REFERER'));
        }

        # body
        if ($this->bAjax) {
            $this->HttpResponse->setContents(json_encode($this->aData));
        } else {
            if ($this->bAutoRender) {
                if ($this->sTPL === null) {
                    $this->sTPL = $this->getDefaultTPL();
                }
                $this->Log->debug('Use template[{tpl}]', array('tpl' => $this->sTPL));

                $this->HttpResponse->setContents(
                    $this->View
                        ->setTpl($this->sTPL)
                        ->assignMulti($this->aData)
                        ->renderAsResult()
                );
            }
        }
    }

    public function innerCall($sController, $sMethod, $aParam = null, $bNoAfterRender = true)
    {
        if ($aParam === null) {
            $aParam = $this->aParam;
        }
        $CallBack = new CallBack($this->Context->sNS, $this->Log);
        $CallBack->setCBObject(
            $sController,
            $sMethod,
            $bNoAfterRender ? array_merge($aParam, array('__INNER_CALL__' => true)) : $aParam
        );
        $CallBack->call();
        return $CallBack->mCallable->aData;
    }

    public function outerCall(Http\Request $HttpRequest = null)
    {
        # 获取一个 Context 副本
        $Context = $this->Context->copy();

        # 复写原始 HttpRequest
        if ($HttpRequest !== null) {
            $Context->register('HttpRequest', $HttpRequest);
        }

        # 运行
        Bootstrap::factoryWithContext(Context::getInst())->run();
    }

    protected function getDefaultTPL()
    {
        $CB        = $this->Context->CallBack;
        $aCallable = $CB->mCallable;
        $sTPL      = str_replace(
            '_',
            DIRECTORY_SEPARATOR,
            str_replace($CB->sNSPre . '\ControllerHttp_', '', get_class($aCallable[0]))
        );
        $sMethod   = substr($aCallable[1], 6);
        if ($sMethod !== 'Default') {
            $sTPL .= "_$sMethod";
        }
        return $sTPL . '.php';
    }
}