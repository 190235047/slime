<?php
namespace Slime\Component\Http;

class HttpRequest extends HttpCommon
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
        $aParam = array(),
        $aHeader = array(),
        $aCookie = array(),
        $aFile = array(),
        $sContent = null,
        $sProtocol = 'HTTP/1.1'
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
            $sURL,
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


    protected $sRequestMethod;
    public function getRequestMethod()
    {
        return $this->sRequestMethod;
    }

    public function setRequestMethod($sRequestMethod)
    {
        $this->sRequestMethod = $sRequestMethod;
        return $this;
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

    public function getRequestURI()
    {
        return $this->sRequestURI;
    }

    public function setRequestURI($sRequestURI)
    {
        $this->sRequestURI = $sRequestURI;
        return $this;
    }

    public function getProtocol()
    {
        return $this->sProtocol;
    }

    public function setProtocol($sProtocol)
    {
        $this->sProtocol = $sProtocol;
        return $this;
    }

    public function isAjax()
    {
        return strtolower($this->Header['X_Requested_With']) == 'xmlhttprequest';
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

    public function getCookie($mKeyOrKeys, $bXssFilter = false)
    {
        return $this->_get($this->Cookie, $mKeyOrKeys, $bXssFilter);
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

    protected function tidyHeader()
    {
        # GET LOGIC
        $aArr = parse_url($this->sRequestURI);
        if (empty($aArr['path'])) {
            $aArr['path'] = '/';
        }
        if (empty($aArr['query'])) {
            $aQ = $this->Get->toArray();
        } else {
            parse_str($aArr['query'], $aQ);
            $aQ = array_merge($aQ, $this->Get->toArray());
        }
        $this->sRequestURI = empty($aQ) ? $aArr['path'] : ($aArr['path'] . '?' . http_build_query($aQ));

        if ($this->sRequestMethod === 'POST' && count($this->Post) > 0) {
            $this->sContent = http_build_query($this->Post->toArray());
            if ($this->Header['Content-Type'] === null) {
                $this->Header['Content-Type'] = 'application/x-www-form-urlencoded';
            }
        }

        # preset header
        if ($this->Header['Transfer-Encoding'] !== 'chunked'
            && $this->Header['Content-Length'] === null
            && $this->sContent !== null
            && strlen($this->sContent) > 0
        ) {
            $this->Header['Content-Length'] = strlen($this->sContent);
        }
        if ($this->Header['Content-Type'] === null) {
            $this->Header['Content-Type'] = 'text/html; charset=utf-8';
        }
    }

    //------------------- call logic -----------------------

    public function call()
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

        $mResult = HttpResponse::createFromResponseString($mData);

        RET_callByCurl:
            curl_close($rCurl);
            return $mResult;
    }
}