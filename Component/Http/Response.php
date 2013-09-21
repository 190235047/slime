<?php
namespace SlimeFramework\Component\Http;

class Response
{
    public $sStatus;

    public $aHeader = array();
    public $aHeaderCookie = array();
    public $sContent;

    public static function factory()
    {
        return new self();
    }

    public function getHeader($sKey) {
        return isset($this->aHeader[$sKey]) ? $this->aHeader[$sKey] : null;
    }

    public function setHeader($sKey, $sValue, $bOverwrite = true)
    {
        if ($bOverwrite || (!$bOverwrite && !isset($this->aHeader[$sKey]))) {
            $this->aHeader[$sKey] = $sValue;
        }
        return $this;
    }

    public function setCookie($sName, $sValue, $iExpire = null, $sPath = null, $sDomain = null, $bSecure = null, $bHttpOnly = null)
    {
        $this->aHeaderCookie[$sName] = array(
            'value' => $sValue,
            'expire' => $iExpire,
            'path' => $sPath,
            'domain' => $sDomain,
            'is_secure' => $bSecure,
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

    public function setContents($sContent)
    {
        $this->sContent = $sContent;
        return $this;
    }

    /**
     * Sends HTTP headers.
     *
     * @return Response
     */
    public function sendHeaders()
    {
        // headers have already been sent by the developer
        if (headers_sent()) {
            return $this;
        }

        // headers
        foreach ($this->aHeader as $sK => $sV) {
            header($sK.': '.$sV);
        }

        // cookies
        foreach ($this->aHeaderCookie as $sName => $aCookie) {
            setcookie($sName,
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
     *
     * @api
     */
    public function send()
    {
        $this->sendHeaders()->sendContent();

        return $this;
    }
}