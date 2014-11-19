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
 * @property-read Bag_Param    $BagCOOKIE
 * @property-read Bag_File     $BagFILE
 * @property-read Bag_Param    $BagGPC
 */
class REQ
{
    public static function createFromGlobal()
    {
        return new self($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES, $_REQUEST);
    }

    protected $aSERVER;

    /**
     * @param array $aSERVER
     * @param array $aGET
     * @param array $aPOST
     * @param array $aCOOKIE
     * @param array $aFILE
     * @param array $aREQUEST
     */
    public function __construct($aSERVER, $aGET, $aPOST, $aCOOKIE, $aFILE, $aREQUEST)
    {
        $this->aSERVER   = $aSERVER;
        $this->BagGET    = new Bag_Param($aGET);
        $this->BagPOST   = new Bag_Param($aPOST);
        $this->BagCOOKIE = new Bag_Param($aCOOKIE);
        $this->BagFILE   = new Bag_File($aFILE);
        $this->BagGPC    = new Bag_Param($aREQUEST);
    }

    public function __get($sKey)
    {
        return $this->$sKey;
    }

    /**
     * @return string
     */
    public function getRequestMethod()
    {
        return (string)$this->aSERVER['REQUEST_METHOD'];
    }

    /**
     * @return string
     */
    public function getRequestURI()
    {
        return (string)$this->aSERVER['REQUEST_URI'];
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        return (string)$this->aSERVER['SERVER_PROTOCOL'];
    }

    /**
     * @param $sKey
     *
     * @return null|string
     */
    public function getHeader($sKey)
    {
        $sKeyFix = 'HTTP_' . strtoupper($sKey);
        return isset($this->aSERVER[$sKeyFix]) ? $this->aSERVER[$sKeyFix] : null;
    }

    /**
     * @param string $m_n_sKey_aKeys
     *
     * @return array|null|string
     */
    public function getC($m_n_sKey_aKeys)
    {
        return $this->BagCOOKIE->find($m_n_sKey_aKeys);
    }

    /**
     * @param string $m_n_sKey_aKeys
     *
     * @return array|null|string
     */
    public function getG($m_n_sKey_aKeys)
    {
        return $this->BagGET->find($m_n_sKey_aKeys);
    }

    /**
     * @param string $m_n_sKey_aKeys
     *
     * @return array|null|string
     */
    public function getP($m_n_sKey_aKeys)
    {
        return $this->BagPOST->find($m_n_sKey_aKeys);
    }

    /**
     * @param string $m_n_sKey_aKeys
     *
     * @return array|null|string
     */
    public function getGPC($m_n_sKey_aKeys)
    {
        return $this->BagGPC->find($m_n_sKey_aKeys);
    }

    /**
     * @return string|bool
     */
    public function getBody()
    {
        return file_get_contents('php://input');
    }

    /**
     * @return bool
     */
    public function isAjax()
    {
        return strtolower($this->getHeader('X_Requested_With')) === 'xmlhttprequest';
    }

    /**
     * @return string
     */
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