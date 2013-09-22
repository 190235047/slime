<?php
namespace SlimeFramework\Core;

use SlimeFramework\Component\View\Viewer;

abstract class Controller_Http
{
    protected $Context;

    protected $sTPL  = null;
    protected $aData = array();

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

        $this->bGet  = $this->HttpRequest->getRequestMethod() === 'GET';
        $this->bAjax = $this->HttpRequest->isAjax();
    }

    public function __after__()
    {
        if ($this->bGet) {
            # header
            if ($this->HttpResponse->getHeader('Content-Type')!==null) {
                if ($this->bAjax) {
                    $this->HttpResponse->setHeader('Content-Type', 'application/javascript; charset=utf-8', false);
                } else {
                    $this->HttpResponse->setHeader('Content-type', 'text/html; charset=utf-8', false);
                }
            }

            # body
            if ($this->sTPL === null) {
                $CB = $this->Context->CallBack;
                $aCallable = $CB->mCallable;
                $this->sTPL = str_replace('_', DIRECTORY_SEPARATOR,
                    str_replace($CB->sNSPre . '\ControllerHttp_', '', get_class($aCallable[0]))
                );
                $sMethod = substr($aCallable[1], 6);
                if ($sMethod!=='Default') {
                    $this->sTPL .= "_$sMethod";
                }
            }
            $this->sTPL .= '.php';

            $this->HttpResponse->setContents(
                $this->View
                    ->setTpl($this->sTPL)
                    ->assignMulti($this->aData)
                    ->renderAsResult()
            );
        }
    }
}