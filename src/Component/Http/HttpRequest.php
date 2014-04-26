<?php
namespace Slime\Component\Http;

/**
 * Class HttpRequest
 *
 * @package Slime\Component\Http
 * @author  smallslime@gmail.com
 *
 * @property-read array        $aSERVER
 * @property-read Bag_Param    $BagGET
 * @property-read Bag_Param    $BagPOST
 * @property-read Bag_Cookie   $BagCOOKIE
 * @property-read Bag_File     $BagFILE
 * @property-read Bag_Param    $BagGP
 */
class HttpRequest
{
    protected $aSERVER;

    public function __construct(
        $aSERVER = null,
        $aGET = null,
        $aPOST = null,
        $aCOOKIE = null,
        $aFILE = null,
        $aREQUEST = null
    ) {
        $this->aSERVER   = empty($aSERVER) ? $_SERVER : $aSERVER;
        $this->BagGET    = new Bag_Param(empty($aGET) ? $_GET : $aGET, $this->bEnable);
        $this->BagPOST   = new Bag_Param(empty($aPOST) ? $_POST : $aPOST, $this->bEnable);
        $this->BagCOOKIE = new Bag_Cookie(empty($aCOOKIE) ? $_COOKIE : $aCOOKIE, $this->bEnable);
        $this->BagFILE   = new Bag_File(empty($aFILE) ? $_FILES : $aFILE, $this->bEnable);
        $this->BagGP     = new Bag_Param(empty($aREQUEST) ? $_REQUEST : $aREQUEST, $this->bEnable);
    }
    
    private $bEnable;
    public function setXSSEnable($bEnable = true)
    {
        $this->bEnable = $bEnable;
    }

    public function __get($sKey)
    {
        return $this->$sKey;
    }

    public function getRequestMethod()
    {
        return $this->aSERVER['REQUEST_METHOD'];
    }

    public function getRequestURI()
    {
        return $this->aSERVER['REQUEST_URI'];
    }

    public function getProtocol()
    {
        return $this->aSERVER['SERVER_PROTOCOL'];
    }

    // request_header
    public function getHeader($sKey)
    {
        $sKeyFix = 'HTTP_' . strtoupper($sKey);
        return isset($this->aSERVER[$sKeyFix]) ? $this->aSERVER[$sKeyFix] : null;
    }

    // request_header_cookie
    public function getC($saKeyOrKeys)
    {
        return $this->BagCOOKIE->find($saKeyOrKeys);
    }

    // request_header_get
    public function getG($saKeyOrKeys)
    {
        return $this->BagGET->find($saKeyOrKeys);
    }

    // request_header_post
    public function getP($saKeyOrKeys)
    {
        return $this->BagPOST->find($saKeyOrKeys);
    }

    // request_header_all
    public function getGP($saKeyOrKeys)
    {
        return $this->BagGP->find($saKeyOrKeys);
    }

    // request_content
    public function getBody()
    {
        return file_get_contents('php://input');
    }

    // is ajax
    public function isAjax()
    {
        return strtolower($this->getHeader('X_Requested_With')) === 'xmlhttprequest';
    }

    // ip
    public function getClientIP()
    {
        $sIp = '';
        if (!empty($this->aSERVER['HTTP_CLIENT_IP'])) {
            $sIp = $this->aSERVER['HTTP_CLIENT_IP'];
            goto RET;
        }
        if (
            !empty($this->aSERVER['HTTP_X_FORWARDED_FOR']) &&
            strcasecmp($this->aSERVER['HTTP_X_FORWARDED_FOR'], 'unknown')
        ) {
            $sTmpIp = $this->aSERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (
            !empty($this->aSERVER['REMOTE_ADDR']) &&
            strcasecmp($this->aSERVER['REMOTE_ADDR'], 'unknown')
        ) {
            $sTmpIp = $this->aSERVER['REMOTE_ADDR'];
        } else {
            $sTmpIp = '';
        }
        if ($sTmpIp === '') {
            goto RET;
        }
        $aIp = explode(',', $sTmpIp);
        if (count($aIp) === 1) {
            $sIp = $aIp[0];
            goto RET;
        }
        foreach ($aIp as $sOneIp) {
            if (ip2long($sOneIp) !== false) {
                $sIp = $sOneIp;
                break;
            }
        }

        RET:
        return $sIp;
    }
}