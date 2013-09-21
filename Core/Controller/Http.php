<?php
namespace SlimeFramework\Core;

use SlimeFramework\Component\View\Viewer;

abstract class Controller_Http
{
    protected $Context;

    protected $aData = array();

    public function __construct(array $aParam = array())
    {
        $this->Context      = $Context = Context::getInst();
        $this->Log          = $Context->Log;
        $this->HttpRequest  = $Context->HttpRequest;
        $this->HttpResponse = $Context->HttpResponse;
        $this->aParam       = $aParam;
        $this->View         = Viewer::factory('PHP', $this->Log)->setBaseDir(DIR_VIEW);
    }

    protected function __before__()
    {
    }

    protected function __after__()
    {
        if ($this->HttpRequest->getRequestMethod() === 'GET') {
            if ($this->HttpResponse->getHeader('Content-Type')!==null) {
                if ($this->HttpRequest->isAjax()) {
                    $this->HttpResponse->setHeader('Content-Type', 'application/javascript; charset=utf-8', false);
                } else {
                    $this->HttpResponse->setHeader('Content-type', 'text/html; charset=utf-8');
                }
            }

            $this->View->assignMulti($this->aData);
        }
    }
}