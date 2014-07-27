<?php
namespace Slime\Component\Http;

/**
 * Class HttpResponse
 *
 * @package Slime\Component\Http
 * @author  smallslime@gmail.com
 */
class HttpResponse
{
    public function __construct()
    {
        $this->BagHeader = new Bag_Base();
    }

    /** @var int */
    protected $iStatus = 200;

    /**
     * @param int $iCode
     */
    public function setResponseCode($iCode = 200)
    {
        $this->iStatus = $iCode;
    }

    /**
     * @return int
     */
    public function getResponseCode()
    {
        return $this->iStatus;
    }

    /** @var string */
    protected $sProtocol = 'HTTP/1.1';

    /**
     * @param string $sProtocol
     */
    public function setProtocol($sProtocol = 'HTTP/1.1')
    {
        $this->sProtocol = $sProtocol;
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        return $this->sProtocol;
    }

    /** @var Bag_Base */
    protected $BagHeader;

    /**
     * @param array | string $saKeyOrKVMap
     * @param null | string  $nsValue
     * @param bool           $bOverwrite
     */
    public function setHeader($saKeyOrKVMap, $nsValue = null, $bOverwrite = true)
    {
        $this->BagHeader->set($saKeyOrKVMap, $nsValue, $bOverwrite);
    }

    /**
     * @param string $sKey
     *
     * @return mixed
     */
    public function getHeader($sKey)
    {
        return $this->BagHeader[$sKey];
    }

    /**
     * @param bool $bOverwrite
     */
    public function setHeaderNoCache($bOverwrite = false)
    {
        $this->BagHeader->set(
            array(
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma'        => 'no-cache',
                'Expires'       => 'Expires: Mon, 26 Jul 1997 05:00:00 GMT'
            ),
            $bOverwrite
        );
    }

    /**
     * @param string $sURL
     * @param int    $iCode
     * @param bool   $bOverwrite
     */
    public function setHeaderRedirect($sURL, $iCode = null, $bOverwrite = true)
    {
        if ($iCode !== null) {
            $this->iStatus = $iCode;
        }

        $this->BagHeader->set(array('Location' => $sURL), $bOverwrite);
    }

    /** @var array */
    protected $aPreCookie = array();

    /**
     * @param string      $sName
     * @param string      $sValue
     * @param int|null    $iExpire
     * @param string|null $sPath
     * @param string|null $sDomain
     * @param bool|null   $bSecure
     * @param bool|null   $bHttpOnly
     *
     * @return HttpResponse
     */
    public function setCookie(
        $sName,
        $sValue,
        $iExpire = null,
        $sPath = null,
        $sDomain = null,
        $bSecure = null,
        $bHttpOnly = null
    ) {
        $this->aPreCookie[$sName] = array(
            'value'       => $sValue,
            'expire'      => $iExpire,
            'path'        => $sPath,
            'domain'      => $sDomain,
            'is_secure'   => $bSecure,
            'is_httponly' => $bHttpOnly
        );
        return $this;
    }

    /**
     * @return array
     */
    public function getCookiePre()
    {
        return $this->aPreCookie;
    }

    /** @var string */
    protected $sContent;

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->sContent;
    }

    /**
     * @param string $sContent
     */
    public function setContent($sContent)
    {
        $this->sContent = $sContent;
    }

    /**
     * Sends content for the current web response.
     */
    public function sendContent()
    {
        echo $this->sContent;
    }

    /**
     * Sends HTTP headers.
     */
    public function sendHeader()
    {
        // headers have already been sent by the developer
        if (headers_sent()) {
            trigger_error('header has sent', E_USER_NOTICE);
            return;
        }

        // first line
        if ($this->iStatus !== 200) {
            header(sprintf('%s %d %s', $this->sProtocol, $this->iStatus, Helper_HttpStatus::getString($this->iStatus)));
        }

        // headers
        foreach ($this->BagHeader->aData as $sK => $sV) {
            header($sK . ': ' . $sV);
        }

        // cookies
        foreach ($this->aPreCookie as $sName => $aCookie) {
            setcookie(
                $sName,
                $aCookie['value'],
                $aCookie['expire'],
                $aCookie['path'],
                $aCookie['domain'],
                $aCookie['is_secure'],
                $aCookie['is_httponly']
            );
        }
    }

    /**
     * Sends HTTP headers and content.
     */
    public function send()
    {
        $this->sendHeader();
        $this->sendContent();
    }
}