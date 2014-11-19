<?php
namespace Slime\Component\Http;

/**
 * Class HttpResponse
 *
 * @package Slime\Component\Http
 * @author  smallslime@gmail.com
 */
class RESP
{
    public static function getString($iStatus)
    {
        static $aStatusTexts = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing', // RFC2518
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status', // RFC4918
            208 => 'Already Reported', // RFC5842
            226 => 'IM Used', // RFC3229
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => 'Reserved',
            307 => 'Temporary Redirect',
            308 => 'Permanent Redirect', // RFC-reschke-http-status-308-07
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'I\'m a teapot', // RFC2324
            422 => 'Unprocessable Entity', // RFC4918
            423 => 'Locked', // RFC4918
            424 => 'Failed Dependency', // RFC4918
            425 => 'Reserved for WebDAV advanced collections expired proposal', // RFC2817
            426 => 'Upgrade Required', // RFC2817
            428 => 'Precondition Required', // RFC6585
            429 => 'Too Many Requests', // RFC6585
            431 => 'Request Header Fields Too Large', // RFC6585
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates (Experimental)', // RFC2295
            507 => 'Insufficient Storage', // RFC4918
            508 => 'Loop Detected', // RFC5842
            510 => 'Not Extended', // RFC2774
            511 => 'Network Authentication Required', // RFC6585
        );

        if (isset($aStatusTexts[$iStatus])) {
            return $aStatusTexts[$iStatus];
        } else {
            trigger_error(E_USER_WARNING, 'unknown status code');
            return 'Unknown';
        }
    }

    public function __construct($niCode = null, array $naHeader = null, $nsBody = null)
    {
        $this->BagHeader = new Bag_Base();

        if ($niCode !== null) {
            $this->setResponseCode($niCode);
        }
        if ($naHeader !== null) {
            $this->setHeaders($naHeader);
        }
        if ($nsBody !== null) {
            $this->setBody($nsBody);
        }
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
     * @param string $sK
     * @param string $sV
     * @param bool   $bOverwrite
     *
     * @return $this
     */
    public function setHeader($sK, $sV, $bOverwrite = true)
    {
        $this->BagHeader->set($sK, $sV, $bOverwrite);

        return $this;
    }

    /**
     * @param array $aKV
     * @param bool  $bOverwrite
     *
     * @return $this
     */
    public function setHeaders($aKV, $bOverwrite = true)
    {
        $this->BagHeader->setMulti($aKV, $bOverwrite);

        return $this;
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
     *
     * @return $this
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

        return $this;
    }

    /**
     * @param string $sURL
     * @param int    $iCode
     * @param bool   $bOverwrite
     *
     * @return $this
     */
    public function setHeaderRedirect($sURL, $iCode = null, $bOverwrite = true)
    {
        if ($iCode !== null) {
            $this->iStatus = $iCode;
        }

        $this->BagHeader->set(array('Location' => $sURL), $bOverwrite);

        return $this;
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
     * @return RESP
     */
    public function setCookiePre(
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
    protected $sBody;

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->sBody;
    }

    /**
     * @param string $sBody
     *
     * @return $this
     */
    public function setBody($sBody)
    {
        $this->sBody = $sBody;

        return $this;
    }

    /**
     * Sends content for the current web response.
     */
    public function sendBody()
    {
        echo $this->sBody;
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
            header(sprintf('%s %d %s', $this->sProtocol, $this->iStatus, self::getString($this->iStatus)));
        }

        // headers
        foreach ($this->BagHeader->getData() as $sK => $sV) {
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
        $this->sendBody();
    }
}