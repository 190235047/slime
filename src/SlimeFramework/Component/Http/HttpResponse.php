<?php
namespace SlimeFramework\Component\Http;

class HttpResponse
{
    public $iStatus;
    public $sProtocol;
    public $sStatusMessage;

    public $Header;
    public $sContent;

    public $aPreCookie;

    public static function create()
    {
        return new self();
    }

    public function __construct()
    {
        $this->Header = new Bag_Header();
    }

    public function getHeader($sKey)
    {
        return $this->Header[$sKey];
    }

    public function setHeader($sKey, $sValue)
    {
        if ($sValue===null) {
            unset($this->Header[$sKey]);
        }
        $this->Header[$sKey] = $sValue;
        return $this;
    }

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

    public function setNoCache()
    {
        $this->setHeader('Cache-Control', 'no-cache');
        $this->setHeader('pragma', 'no-cache');
        $this->setHeader('expires', '-1');
        return $this;
    }

    public function setRedirect($sURL, $iCode = null)
    {
        $this->setHeader('Location', $sURL);
        return $this;
    }

    public function unsetRedirect()
    {
        $this->setHeader('Location', null);
        return $this;
    }

    public function setContent($sContent)
    {
        $this->sContent = $sContent;
        return $this;
    }

    /**
     * Sends HTTP headers.
     *
     * @return HttpResponse
     */
    public function sendHeaders()
    {
        // headers have already been sent by the developer
        if (headers_sent()) {
            return $this;
        }

        // headers
        foreach ($this->Header as $sK => $sV) {
            header($sK . ': ' . $sV);
        }

        // cookies
        foreach ($this->aPreCookie as $sName => $aCookie) {
            setcookie(
                $sName,
                $aCookie['value'],
                $aCookie['expire'],
                isset($aCookie['path']) ? $aCookie['path'] : null,
                isset($aCookie['domain']) ? $aCookie['domain'] : null,
                isset($aCookie['is_secure']) ? $aCookie['is_secure'] : null,
                isset($aCookie['is_httponly']) ? $aCookie['is_httponly'] : null
            );
        }

        return $this;
    }

    /**
     * Sends content for the current web response.
     *
     * @return $this
     */
    public function sendContent()
    {
        echo $this->sContent;

        return $this;
    }

    /**
     * Sends HTTP headers and content.
     *
     * @return $this
     */
    public function send()
    {
        $this->sendHeaders()->sendContent();

        return $this;
    }
}