<?php
namespace Slime\Component\Http;

/**
 * Class REQ_PHP
 *
 * @package Slime\Component\Http
 * @author  smallslime@gmail.com
 *
 */
class REQ
{
    public static function createFromGlobal()
    {
        return new self($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES, $_REQUEST);
    }

    protected $naTidyHeader = null;
    protected $nsBody;

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
        $this->aSERVER = $aSERVER;
        $this->aGET    = $aGET;
        $this->aPOST   = $aPOST;
        $this->aCOOKIE = $aCOOKIE;
        $this->aFILE   = $aFILE;
        $this->aGPC    = $aREQUEST;
    }

    public function preTidyHeader()
    {
        $this->naTidyHeader = array();
        foreach ($this->aSERVER as $sK => $sV) {
            if (substr($sK, 0, 5) === 'HTTP_') {
                $this->naTidyHeader[str_replace('_', '-', substr($sK, 5))] = $sV;;
            }
        }
    }

    /**
     * @param null|string|array $nasK
     *
     * @return null|string|array
     */
    public function getHeader($nasK = null)
    {
        if ($this->naTidyHeader === null) {
            if (is_string($nasK)) {
                return isset($this->aSERVER[$sK = 'HTTP_' . str_replace('-', '_',
                            strtoupper($nasK))]) ? $this->aSERVER[$sK] : null;
            } else {
                $this->preTidyHeader();
                return $this->_getData($nasK, $this->naTidyHeader);
            }
        } else {
            return $this->_getData($nasK, $this->naTidyHeader);
        }
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return file_get_contents('php://input');
    }

    /**
     * @return string upper chars
     */
    public function getMethod()
    {
        return strtoupper($this->aSERVER['REQUEST_METHOD']);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->aSERVER['REQUEST_URI'];
    }

    /**
     * @return string upper chars
     */
    public function getProtocol()
    {
        return strtoupper($this->aSERVER['SERVER_PROTOCOL']);
    }

    /**
     * @param null|string|array $nasK
     *
     * @return null|string|array
     */
    public function getG($nasK = null)
    {
        return $this->_getData($nasK, $this->aGET);
    }

    /**
     * @param null|string|array $nasK
     *
     * @return null|string|array
     */
    public function getP($nasK = null)
    {
        return $this->_getData($nasK, $this->aPOST);
    }

    /**
     * @param null|string|array $nasK
     *
     * @return null|string|array
     */
    public function getC($nasK = null)
    {
        return $this->_getData($nasK, $this->aCOOKIE);
    }

    /**
     * @param null|string|array $nasK
     *
     * @return null|string|array
     */
    public function getGPC($nasK = null)
    {
        return $this->_getData($nasK, $this->aGPC);
    }

    /**
     * @param null|string|array $nasK
     *
     * @return null|string|array
     */
    public function getFile($nasK)
    {
        return $this->_getData($nasK, $this->aFILE);
    }


    /**
     * @return bool
     */
    public function isAjax()
    {
        return strtolower($this->getHeader('X_Requested_With')) === 'xmlhttprequest';
    }

    /**
     * @return null|string
     */
    public function guessClientIP()
    {
        if (!empty($this->aSERVER['HTTP_CLIENT_IP'])) {
            return $this->aSERVER['HTTP_CLIENT_IP'];
        }

        if (!empty($this->aSERVER['HTTP_X_FORWARDED_FOR']) &&
            strcasecmp($this->aSERVER['HTTP_X_FORWARDED_FOR'], 'unknown')
        ) {
            $sTmpIp = $this->aSERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($this->aSERVER['REMOTE_ADDR']) &&
            strcasecmp($this->aSERVER['REMOTE_ADDR'], 'unknown')
        ) {
            $sTmpIp = $this->aSERVER['REMOTE_ADDR'];
        } else {
            return null;
        }

        $aIp = explode(',', $sTmpIp);
        if (count($aIp) === 1) {
            return $aIp[0];
        }

        foreach ($aIp as $sOneIp) {
            if (ip2long($sOneIp) !== false) {
                return $sOneIp;
            }
        }

        return null;
    }

    /**
     * @param null|string|array $nasK
     * @param                   $aData
     *
     * @return null|string|array
     */
    protected function _getData($nasK, $aData)
    {
        if ($nasK === null) {
            return $aData;
        } elseif (is_array($nasK)) {
            $aRS = array();
            foreach ($nasK as $sK) {
                $aRS[$sK] = isset($aData[$sK]) ? $aData[$sK] : null;
            }
            return $aRS;
        } else {
            return isset($aData[$sK = (string)$nasK]) ? $aData[$sK] : null;
        }
    }
}