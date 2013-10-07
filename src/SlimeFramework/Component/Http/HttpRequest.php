<?php
namespace SlimeFramework\Component\Http;

class HttpRequest
{
    /** @var Helper_XSS */
    private $XSS;

    /** @var  bool */
    protected $bHasXssPreDeal = false;

    /**
     * Creates a new request with values from PHP's super globals
     *
     * @param bool $bGetRawData
     *
     * @return HttpRequest A new request
     */
    public static function createFromGlobals($bGetRawData = false)
    {
        $Header = new Bag_Header();
        if (!empty($aServer)) {
            foreach ($aServer as $sK => $sV) {
                if (substr($sK, 0, 5) === 'HTTP_') {
                    $Header[substr($sK, 5)] = $sV;
                }
            }
        }
        return new self(
            $_SERVER['SERVER_PROTOCOL'],
            $_SERVER['REQUEST_METHOD'],
            $_SERVER['REQUEST_URI'],
            $Header,
            (!$bGetRawData || $_SERVER['REQUEST_METHOD'] === 'GET') ? '' : file_get_contents('php://input'),
            new Bag_Get($_GET),
            new Bag_Post($_POST),
            new Bag_Cookie($_COOKIE),
            new Bag_File($_FILES)
        );
    }

    /**
     * Creates a Request based on a given URI and configuration.
     * The information contained in the URI always take precedence
     * over the other information (server and parameters).
     *
     * @param string $sMethod     The HTTP method
     * @param string $sURL        The URL
     * @param string $sProtocol   协议
     * @param array  $aHeader     The Header KVPair
     * @param array  $aParam      The query (GET) or request (POST) parameters
     * @param array  $aCookie     The request cookies ($_COOKIE)
     * @param array  $aFile       The request files ($_FILES)
     * @param string $sContent    The raw body data
     *
     * @return HttpRequest A Request instance
     */
    public function create(
        $sMethod = 'GET',
        $sURL,
        $sProtocol = 'http/1.1',
        $aHeader = array(),
        $aParam = array(),
        $aCookie = array(),
        $aFile = array(),
        $sContent = null
    ) {
        $aArr = parse_url($sURL);
        if (!isset($aHeader['Host'])) {
            $aHeader['Host'] = $aArr['port'] == 80 ? $aArr['host'] : "{$aArr['host']}:{$aArr['port']}";
        }
        $Header = new Bag_Header($aHeader);
        if ($this->sRequestMethod === 'GET') {
            $Get  = new Bag_Get($aParam);
            $Post = new Bag_Post();
        } else {
            $Get  = new Bag_Get();
            $Post = new Bag_Post($aParam);
        }
        $this->Header['File'] = new Bag_File($aFile);
        $this->sContent       = $sContent;
        return new self(
            $sProtocol,
            $sMethod,
            $aArr['path'],
            $Header,
            $sContent,
            $Get,
            $Post,
            new Bag_Cookie($aCookie),
            new Bag_File($aFile)
        );
    }

    public function __construct(
        $sProtocol,
        $sMethod,
        $sRequestURI,
        $Header,
        $sContent,
        Bag_Get $Get,
        Bag_Post $Post,
        Bag_Cookie $Cookie,
        Bag_File $File
    ) {
        $this->sProtocol      = $sProtocol;
        $this->sRequestMethod = strtoupper($sMethod);
        $this->sRequestURI    = $sRequestURI;
        $this->Header         = $Header;
        $this->sContent       = $sContent;

        $this->Get              = $Get;
        $this->Post             = $Post;
        $this->Cookie           = $Cookie;
        $this->File             = $File;
        $this->Header['Cookie'] = $Cookie;
    }

    public function preDealXss($sXSSCharset = 'UTF-8')
    {
        $this->XSS->setCharset($sXSSCharset);
        $this->Get            = new Bag_Get($this->getXSSLib()->xss_clean($this->Get->getArrayCopy()));
        $this->Post           = new Bag_Get($this->getXSSLib()->xss_clean($this->Post->getArrayCopy()));
        $this->Cookie         = new Bag_Get($this->getXSSLib()->xss_clean($this->Cookie->getArrayCopy()));
        $this->bHasXssPreDeal = true;
    }

    public function getXSSLib()
    {
        if ($this->XSS === null) {
            $this->XSS = new Helper_XSS();
        }
        return $this->XSS;
    }

    public function getRequestMethod()
    {
        return $this->sRequestMethod;
    }

    public function getRequestURI()
    {
        return $this->sRequestURI;
    }

    public function getProtocol()
    {
        return $this->sProtocol;
    }

    public function getHeader($sKey)
    {
        return $this->Header[$sKey];
    }

    public function getCookie($mKeyOrKeys, $bXssFilter = false)
    {
        return $this->_get($this->Header['Cookie'], $mKeyOrKeys, $bXssFilter);
    }

    public function getContents()
    {
        return $this->sContent;
    }

    public function getGet($mKeyOrKeys, $bXssFilter = false)
    {
        return $this->_get($this->Get, $mKeyOrKeys, $bXssFilter);
    }

    public function getPost($mKeyOrKeys, $bXssFilter = false)
    {
        return $this->_get($this->Post, $mKeyOrKeys, $bXssFilter);
    }

    public function getGetPost($mKeyOrKeys, $bGetFirst = true, $bXssFilter = null)
    {
        if ($bGetFirst) {
            $Q1 = $this->Get;
            $Q2 = $this->Post;
        } else {
            $Q1 = $this->Post;
            $Q2 = $this->Get;
        }
        if (is_array($mKeyOrKeys)) {
            $mRS = array();
            foreach ($mKeyOrKeys as $sKey) {
                $mRS[$sKey] = $Q1[$sKey]===null ? (isset($Q2[$sKey]) ? $Q2[$sKey] : null) : $Q1[$sKey];
            }
        } else {
            $mRS = $Q1[$mKeyOrKeys]===null ? (isset($Q2[$mKeyOrKeys]) ? $Q2[$mKeyOrKeys] : null) : $Q1[$mKeyOrKeys];
        }
        if ($bXssFilter && !$this->bHasXssPreDeal) {
            $mRS = $this->getXSSLib()->xss_clean($mRS);
        }
        return $mRS;
    }

    protected function _get($aArr, $mKeyOrKeys, $bXssFilter)
    {
        if (is_array($mKeyOrKeys)) {
            $mRS = array();
            foreach ($mKeyOrKeys as $sKey) {
                $mRS[$sKey] = $aArr[$sKey];
            }
        } else {
            $mRS = $aArr[$mKeyOrKeys];
        }
        if ($bXssFilter && !$this->bHasXssPreDeal) {
            $mRS = $this->getXSSLib()->xss_clean($mRS);
        }
        return $mRS;
    }

    public function isAjax()
    {
        return strtolower($this->getHeader('X_REQUESTED_WITH')) == 'xmlhttprequest';
    }

    //------------------- call logic -----------------------

    public function call()
    {
        return self::read($this->_call());
    }

    public function callAsync()
    {
        return $this->_call(false);
    }

    private function _call($bBlock = true)
    {
        # GET LOGIC
        if ($this->sRequestMethod === 'GET' && count($this->Get) > 0) {
            $aArr = parse_url($this->sRequestURI);
            if (empty($aArr['query'])) {
                $aQ = $this->Get->getArrayCopy();
            } else {
                parse_str($aArr['query'], $aQ);
                $aQ = array_merge($aArr['query'], $aQ);
            }
            $aArr['query'] = http_build_query($aQ);
            $this->sRequestURI = http_build_url($aArr);
        }

        # POST LOGIC
        if ($this->sRequestMethod === 'POST' && count($this->Post) > 0) {
            $this->sContent = http_build_query($this->Post->getArrayCopy());
            if ($this->Header['Content-Type']===null) {
                $this->Header['Content-Type'] = 'application/x-www-form-urlencoded';
            }
        }

        # preset header
        if ($this->Header['Content-Length']===null && $this->sContent!==null && strlen($this->sContent)>0) {
            $this->Header['Content-Length'] = strlen($this->sContent);
        }
        if ($this->Header['Content-Type']===null) {
            $this->Header['Content-Type'] = 'text/html; charset=utf-8';
        }

        # sock open
        $aArr  = explode($this->Header['HOST'], ':', 2);

        $rSock = fsockopen($this->Header['HOST'], empty($aArr[1]) ? 80 : $aArr[1]);
        socket_set_blocking($rSock, $bBlock);
        fwrite($rSock, sprintf("%s %s %s\r\n", $this->sRequestMethod, $this->sRequestURI, $this->sProtocol));

        # 写 header
        foreach ($this->Header as $sK => $mV) {
            fwrite($rSock, sprintf("%s: %s", $sK, (string)$mV));
        }
        fwrite($rSock, "\r\n");

        # 写 body
        fwrite($rSock, (string)$this->sContent);

        return $rSock;
    }

    /**
     * @param resource $rSock
     * @param int      $iErrCode
     * @param string   $sErrMsg
     *
     * @return null|HttpResponse
     */
    public static function read($rSock, &$iErrCode = 0, &$sErrMsg = '')
    {
        $HttpResponse = new HttpResponse();
        while ($sLine = fgets($rSock) !== false) {
            if (trim($sLine) !== '') {
                break;
            }
        }
        if (empty($sLine)) {
            $iErrCode = 1;
            $sErrMsg  = 'none result';
            return null;
        }
        $aArr = explode(' ', $sLine, 3);
        $HttpResponse->iStatus = (int)$aArr[0];
        $HttpResponse->sProtocol = (string)$aArr[1];
        $HttpResponse->sStatusMessage = (string)$aArr[2];

        $iContentLen = null;
        while ($sLine = fgets($rSock)) {
            if ($sLine === "\r\n") {
                if ($iContentLen === null) {
                    $iErrCode = 2;
                    $sErrMsg  = 'no content-length set!';
                    return null;
                }
                $HttpResponse->setContent(fread($rSock, $iContentLen));
                break;
            } else {
                $aArr = explode(':  ', $sLine, 2);
                $sKey = ($aArr[0]);
                $aArr[1] = isset($aArr[1]) ? ltrim($aArr[1]) : '';
                if (strtoupper($sKey) === 'CONTENT-LENGTH') {
                    $iContentLen = (int)$aArr[1];
                }
                $HttpResponse->setHeader($sKey, $aArr[1]);
            }
        }

        return $HttpResponse;
    }

    /**
     * @param HttpRequest[] $aRequest
     * @param int       $iTimeout
     * @param int       $iInterval 微秒
     *
     * @return HttpResponse[]
     */
    public static function callMulti(array $aRequest, $iTimeout = 10, $iInterval = 100000)
    {
        $aArr = $aResult = array();
        foreach ($aRequest as $mK => $Request) {
            $aArr[$mK]    = $Request->callAsync();
            $aResult[$mK] = null;
        }

        $iHasResponse = 0;
        $iAllResponse = count($aArr);
        $iT1          = time();
        while ($iHasResponse < $iAllResponse) {
            foreach ($aArr as $mK => $rSock) {
                if (($Response = self::read($rSock)) !== null) {
                    $aResult[$mK] = $Response;
                    $iHasResponse++;
                }
            }
            if (time() - $iT1 >= $iTimeout) {
                break;
            }
            usleep($iInterval);
        }

        return $aResult;
    }
}