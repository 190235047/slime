<?php
namespace SlimeFramework\Core;

use SlimeFramework\Component\View\Viewer;

abstract class Controller_Http
{
    protected $Context;

    protected $sTPL  = null;
    protected $aData = array();

    protected $bAutoRender;
    protected $bGet;
    protected $bAjax;

    public function __construct(array $aParam = array())
    {
        $this->Context      = $Context = Context::getInst();
        $this->Log          = $Context->Log;
        $this->Config       = $Context->Config;
        $this->HttpRequest  = $Context->HttpRequest;
        $this->HttpResponse = $Context->HttpResponse;
        $this->aParam       = $aParam;
        $this->View         = Viewer::factory('@PHP', $this->Log)->setBaseDir(DIR_VIEW);
        $this->bGet         = $this->HttpRequest->getRequestMethod() === 'GET';
        $this->bAutoRender  = $this->bGet;
        $this->bAjax        = $this->HttpRequest->isAjax();
    }

    public function __after__()
    {
        if ($this->bAutoRender) {
            # header
            if ($this->HttpResponse->getHeader('Content-Type')===null) {
                if ($this->bAjax) {
                    $this->HttpResponse->setHeader('Content-Type', 'application/javascript; charset=utf-8', false);
                } else {
                    $this->HttpResponse->setHeader('Content-type', 'text/html; charset=utf-8', false);
                }
            }

            # body
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

    protected function getDefaultTPL()
    {
        $CB = $this->Context->CallBack;
        $aCallable = $CB->mCallable;
        $sTPL = str_replace('_', DIRECTORY_SEPARATOR,
            str_replace($CB->sNSPre . '\ControllerHttp_', '', get_class($aCallable[0]))
        );
        $sMethod = substr($aCallable[1], 6);
        if ($sMethod!=='Default') {
            $sTPL .= "_$sMethod";
        }
        return $sTPL . '.php';
    }
}