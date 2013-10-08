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
        foreach ($_SERVER as $sK => $sV) {
            if (substr($sK, 0, 5) === 'HTTP_') {
                $sKK          = implode(
                    '_',
                    array_map(
                        function ($sItem) {
                            return ucfirst(strtolower($sItem));
                        },
                        explode('_', substr($sK, 5))
                    )
                );
                $Header[$sKK] = $sV;
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
     * @param array  $aHeader     The Header KeyValue pair
     * @param array  $aParam      The query (GET) or request (POST) parameters
     * @param array  $aCookie     The request cookies ($_COOKIE)
     * @param array  $aFile       The request files ($_FILES)
     * @param string $sContent    The raw body data
     *
     * @return HttpRequest A Request instance
     */
    public static function create(
        $sMethod = 'GET',
        $sURL,
        $sProtocol = 'HTTP/1.1',
        $aHeader = array(),
        $aParam = array(),
        $aCookie = array(),
        $aFile = array(),
        $sContent = null
    ) {
        $aArr = array_replace(array('port' => 80, 'path' => '/'), parse_url($sURL));
        if (!isset($aHeader['Host'])) {
            $aHeader['Host'] = $aArr['port'] == 80 ? $aArr['host'] : "{$aArr['host']}:{$aArr['port']}";
        }
        $Header = new Bag_Header($aHeader);
        if ($sMethod === 'GET') {
            $Get  = new Bag_Get($aParam);
            $Post = new Bag_Post();
        } else {
            $Get  = new Bag_Get();
            $Post = new Bag_Post($aParam);
        }
        $SELF = new self(
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
        $SELF->tidyHeader();
        return $SELF;
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
        $XSS = $this->getXSSLib();
        $XSS->setCharset($sXSSCharset);
        $this->Get            = new Bag_Get($XSS->xss_clean($this->Get->toArray()));
        $this->Post           = new Bag_Get($XSS->xss_clean($this->Post->toArray()));
        $this->Cookie         = new Bag_Get($XSS->xss_clean($this->Cookie->toArray()));
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
        return $this->_get($this->Cookie, $mKeyOrKeys, $bXssFilter);
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
                $mRS[$sKey] = $Q1[$sKey] === null ? (isset($Q2[$sKey]) ? $Q2[$sKey] : null) : $Q1[$sKey];
            }
        } else {
            $mRS = $Q1[$mKeyOrKeys] === null ? (isset($Q2[$mKeyOrKeys]) ? $Q2[$mKeyOrKeys] : null) : $Q1[$mKeyOrKeys];
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

    protected function tidyHeader()
    {
        # GET LOGIC
        if ($this->sRequestMethod === 'GET' && count($this->Get) > 0) {
            $aArr = parse_url($this->sRequestURI);
            if (empty($aArr['query'])) {
                $aQ = $this->Get->toArray();
            } else {
                parse_str($aArr['query'], $aQ);
                $aQ = array_merge($aArr['query'], $aQ);
            }
            $this->sRequestURI = $aArr['path'] . '?' . http_build_query($aQ);
        } elseif ($this->sRequestMethod === 'POST' && count($this->Post) > 0) {
            $this->sContent = http_build_query($this->Post->toArray());
            if ($this->Header['Content-Type'] === null) {
                $this->Header['Content-Type'] = 'application/x-www-form-urlencoded';
            }
        }

        # preset header
        if ($this->Header['Content-Length'] === null && $this->sContent !== null && strlen($this->sContent) > 0) {
            $this->Header['Content-Length'] = strlen($this->sContent);
        }
        if ($this->Header['Content-Type'] === null) {
            $this->Header['Content-Type'] = 'text/html; charset=utf-8';
        }
    }

    //------------------- call logic -----------------------

    public function callByCurl()
    {
        $aArr = explode('/', $this->sProtocol, 2);
        $rCurl = curl_init(sprintf('%s://%s', $aArr[0], $this->Header['Host'] . $this->sRequestURI));
        curl_setopt($rCurl, CURLOPT_HEADER, 1);
        curl_setopt($rCurl, CURLOPT_RETURNTRANSFER, 1);
        if ($this->sRequestMethod==='POST') {
            curl_setopt($rCurl, CURLOPT_POSTFIELDS, $this->sContent);
        }
        $aHeader = explode("\r\n", rtrim((string)$this->Header, "\r\n"));
        if (!empty($aHeader)) {
            curl_setopt($rCurl, CURLOPT_HTTPHEADER, $aHeader);
        }
        $mData = curl_exec($rCurl);

        if ($mData === false) {
            $mResult = null;
            goto RET_callByCurl;
        }

        $mResult = new HttpResponse();
        $mResult->initFromResponse($mData);

        RET_callByCurl:
            curl_close($rCurl);
            return $mResult;
    }

    public function call($iTimeout = 3, &$iErrNum = 0, &$sErrMsg = '')
    {
        return self::read($this->_call(true, $iTimeout), $iErrNum, $sErrMsg);
    }

    public function callAsync()
    {
        return $this->_call(false, $iErrNum = 0, $sErrMsg = '');
    }

    private function _call($bBlock = true, $iTimeout = 3)
    {
        # first line
        $sStr = sprintf("%s %s %s\r\n", $this->sRequestMethod, $this->sRequestURI, $this->sProtocol);

        # header
        $sStr .= (string)$this->Header;

        # sp
        $sStr .= "\r\n";

        # body
        $sStr .= (string)$this->sContent;

        # open
        $aArr = explode(':', $this->Header['Host'], 2);
        $rSock = fsockopen($this->Header['Host'], isset($aArr[1]) ? $aArr[1] : 80);
        socket_set_blocking($rSock, $bBlock);
        if ($bBlock) {
            //@todo 这里的延迟会影响fread时的速度. 不管怎么设置 fread 都要read多次, 延迟如果设置很小, 结果会快. 这里到底原因是什么???
            stream_set_timeout($rSock, 0, 100000);
        }

        # write
        fwrite($rSock, $sStr);

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
        while (($sLine = fgets($rSock)) !== false) {
            if (trim($sLine) !== '') {
                break;
            }
        }
        if (empty($sLine)) {
            $iErrCode = 1;
            $sErrMsg  = 'http response error in first line';
            return null;
        }
        $aArr                         = explode(' ', $sLine, 3);
        $HttpResponse->iStatus        = (int)$aArr[0];
        $HttpResponse->sProtocol      = (string)$aArr[1];
        $HttpResponse->sStatusMessage = (string)$aArr[2];

        $iContentLen = null;
        while ($sLine = fgets($rSock)) {
            if ($sLine === "\r\n") {
                $sContent = '';
                $iReadBuf = $iContentLen===null ? 1024 * 1024 : $iContentLen * 1024;
                $i = 0;
                while (($sBuf=fread($rSock, $iReadBuf)) != '') {
                    $i++;
                    $sContent .= $sBuf;
                }
                var_dump($i, $sContent);exit;

                $HttpResponse->setContent($sContent);
                /*
                if ($iContentLen === null) {
                    $sContent = '';
                    while (($sLine = fgets($rSock))!==false) {
                        $sContent .= $sLine;
                    }
                    $HttpResponse->setContent($sContent);
                } else {
                    //@todo chunked
                    $HttpResponse->setContent(fread($rSock, $iContentLen * 1024));
                }*/
                break;
            } else {
                $aArr    = explode(': ', $sLine, 2);
                $sKey    = $aArr[0];
                $aArr[1] = isset($aArr[1]) ? ltrim($aArr[1]) : '';
                if ($sKey === 'Content-Length') {
                    $iContentLen = (int)$aArr[1];
                }
                $HttpResponse->setHeader($sKey, $aArr[1]);
            }
        }

        $iErrCode = 0;
        $sErrMsg = '';
        return $HttpResponse;
    }

    /**
     * @param HttpRequest[] $aRequest
     * @param int           $iTimeout
     * @param int           $iInterval 微秒
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