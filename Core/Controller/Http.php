<?php
namespace SlimeFramework\Core;

use SlimeFramework\Component\View\Viewer;

abstract class Controller_Http
{
    protected $Context;

    protected $sTPL = null;
    protected $aData = array();

    /**
     * default : method get make this value as true
     * @var bool
     */
    protected $bAutoRender;

    /**
     * does method is get
     * @var bool
     */
    protected $bGet;

    /**
     * does request is ajax
     * @var bool
     */
    protected $bAjax;

    /**
     * default : (method not get) and (request not ajax) make this value as true
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

    public function __after__()
    {
        # header
        if ($this->HttpResponse->getHeader('Content-Type')===null) {
            if ($this->bAjax) {
                $this->HttpResponse->setHeader('Content-Type', 'application/javascript; charset=utf-8', false);
            } else {
                $this->HttpResponse->setHeader('Content-type', 'text/html; charset=utf-8', false);
            }
        }
        if ($this->bAutoRedirect && $this->HttpResponse->getHeader('Location')===null) {
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

                $this->HttpResponse->setContents(
                    $this->View
                        ->setTpl($this->sTPL)
                        ->assignMulti($this->aData)
                        ->renderAsResult()
                );
            }
        }
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