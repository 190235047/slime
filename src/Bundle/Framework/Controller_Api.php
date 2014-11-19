<?php
namespace Slime\Bundle\Framework;

use Slime\Component\Http;
use Slime\Component\Support\XML;
use Slime\Component\View;

/**
 * Class Controller_API
 * Slime 内置Http控制器基类
 *
 * @package Slime\Bundle\Framework
 * @author  smallslime@gmail.com
 */
abstract class Controller_Api extends Controller_ABS
{
    protected $sDefaultRender = '_renderJSON';
    protected $sJSCBParam = 'cb';
    protected $sXmlTPL = null;
    protected $sJsonTPL = null;
    protected $sJsonPTPL = null;

    protected $aData = array();

    public function __construct($CTX, array $aParam = array())
    {
        parent::__construct($CTX, $aParam);
        $this->REQ  = $this->CTX->REQ;
        $this->RESP = $this->CTX->RESP;
    }

    protected function success(array $aData = array())
    {
        $this->aData['data']    = $aData;
        $this->aData['errCode'] = 0;
        $this->aData['errMsg']  = '';
    }

    protected function fail($sErr, $iErr = 1, array $aData = array())
    {
        $this->aData['data']    = $aData;
        $this->aData['errCode'] = $iErr;
        $this->aData['errMsg']  = $sErr;
    }

    public function __after__()
    {
        if (empty($this->aParam['__ext__'])) {
            $sMethodName = $this->sDefaultRender;
        } else {
            $sMethodName = '_render' . strtoupper($this->aParam['__ext__']);
            if ($this->sDefaultRender !== null && !method_exists($this, $sMethodName)) {
                $sMethodName = $this->sDefaultRender;
            }
        }

        $this->$sMethodName();
    }

    protected function _renderXML()
    {
        $this->RESP
            ->setHeader('Content-Type', 'text/xml; charset=utf-8', false)
            ->setBody(
                $this->sXmlTPL === null ?
                    XML::Array2XML($this->aData) :
                    $this->CTX->View->assignMulti($this->aData)->setTpl($this->sXmlTPL)->renderAsResult()
            );
    }

    protected function _renderJSON()
    {
        $this->RESP
            ->setHeader('Content-Type', 'text/javascript; charset=utf-8', false)
            ->setBody(
                $this->sJsonTPL === null ?
                    json_encode($this->aData) :
                    $this->CTX->View->assignMulti($this->aData)->setTpl($this->sJsonTPL)->renderAsResult()
            );
    }

    protected function _renderJSONP()
    {
        $sCB = $this->REQ->getG($this->sJSCBParam);
        if ($sCB === null) {
            $sCB = 'cb';
        }
        $this->RESP
            ->setHeader('Content-Type', 'text/javascript; charset=utf-8', false)
            ->setBody(
                $this->sJsonPTPL === null ?
                    $sCB . '(' . json_encode($this->aData) . ')' :
                    $this->CTX->View->assignMulti($this->aData)->setTpl($this->sJsonPTPL)->renderAsResult()
            );
    }

    protected function _renderJS()
    {
        $this->_renderJSONP();
    }
}