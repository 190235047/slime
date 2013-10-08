<?php
namespace SlimeFramework\Component\Http;

class HttpResponse extends HttpCommon
{
    protected $iStatus;
    protected $sProtocol;
    protected $sStatusMessage;

    protected $Header;
    protected $sContent;

    protected $aPreCookie = array();

    public static function create()
    {
        return new self();
    }


    public static function createFromResponseString($sStr)
    {
        $SELF = new self();

        list($sHeader, $sContent) = array_replace(array('', ''), explode("\r\n\r\n", ltrim($sStr), 2));
        $aHeader = explode("\r\n", $sHeader);

        foreach ($aHeader as $iK => $sV) {
            if ($iK === 0) {
                list($SELF->sProtocol, $SELF->iStatus, $SELF->sStatusMessage) =
                    array_replace(array('', -1, ''), explode(' ', $sV, 3));
                $SELF->iStatus = (int)$SELF->iStatus;
            } else {
                list($sKey, $sValue) = array_replace(array('', ''), explode(':', $sV, 2));
                $SELF->Header[$sKey] = ltrim($sValue);
            }
        }

        $SELF->sContent = $sContent;

        return $SELF;
    }

    public function __construct()
    {
        $this->Header = new Bag_Header();
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

    public function setRedirect($sURL)
    {
        $this->setHeader('Location', $sURL);
        return $this;
    }

    /**
     * Sends HTTP headers.
     *
     * @return HttpResponse
     */
    public function sendHeader()
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
        $this->sendHeader()->sendContent();

        return $this;
    }
}