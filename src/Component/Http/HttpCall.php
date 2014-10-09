<?php
namespace Slime\Component\Http;

use Slime\Component\Context\Event;

if (!extension_loaded('curl')) {
    throw new \Exception('[EXT] Extension curl is not loaded');
}

/**
 * Class HttpCall
 *
 * @package Slime\Component\Http
 *
 * @method mixed head() head()
 * @method mixed get() get()
 * @method mixed post() post()
 * @method mixed put() put()
 * @method mixed delete() delete()
 */
class HttpCall
{
    protected $sUrl;
    protected $rCurl;

    public static function factory($sUrl = null)
    {
        $Obj        = new self();
        $Obj->rCurl = curl_init($sUrl);
        $Obj->sUrl  = $sUrl;

        return $Obj;
    }

    protected $iConnTimeout = 3000;
    protected $iTimeout = 3000;
    public function setTimeOut($iConnTimeoutMS, $iTimeoutMS)
    {
        $this->iConnTimeout = $iConnTimeoutMS;
        $this->iTimeout     = $iTimeoutMS;

        return $this;
    }

    protected $nsIP = null;
    protected $niPort = null;
    public function setRealHost($sIP, $niPort = null)
    {
        $this->nsIP   = $sIP;
        $this->niPort = $niPort;
    }

    protected $aParam = array();
    public function setParam($saParamKeyOrKVMap, $nsValue = null)
    {
        if (is_array($saParamKeyOrKVMap)) {
            $this->aParam = empty($this->aParam) ? $saParamKeyOrKVMap : array_merge($this->aParam, $saParamKeyOrKVMap);
        } else {
            $this->aParam[(string)$saParamKeyOrKVMap] = (string)$nsValue;
        }
        return $this;
    }


    protected $aFileContentMap = array();
    public function setFileContent($saNameOrNameContentMap, $nsContent = null)
    {
        if (is_array($saNameOrNameContentMap)) {
            $this->aFileContentMap = empty($this->aParam) ?
                $saNameOrNameContentMap :
                array_merge($this->aParam, $saNameOrNameContentMap);
        } else {
            $this->aFileContentMap[(string)$saNameOrNameContentMap] = (string)$nsContent;
        }
        return $this;
    }

    protected $aFileMap = array();
    public function setFile($saNameOrNameFileMap, $nsFile = null)
    {
        if (is_array($saNameOrNameFileMap)) {
            $this->aFileMap = empty($this->aParam) ?
                $saNameOrNameFileMap :
                array_merge($this->aParam, $saNameOrNameFileMap);
        } else {
            $this->aFileMap[(string)$saNameOrNameFileMap] = (string)$nsFile;
        }
        return $this;
    }

    protected $aOpt = array();
    public function setOpt($aOpt)
    {
        $this->aOpt = empty($this->aOpt) ? $aOpt : array_merge($aOpt, $this->aOpt);
    }

    public function __call($sMethod, &$Req)
    {
        $rCurl = $this->rCurl;
        switch (strtoupper($sMethod)) {
            case 'GET':
                break;
            case 'POST':
                curl_setopt($rCurl, CURLOPT_POST, 1);
                break;
            default:
                curl_setopt($rCurl, CURLOPT_CUSTOMREQUEST, $sMethod);
                break;
        }

        $aOpt = $this->aOpt;
        $aOpt[CURLOPT_HTTPHEADER] = array();

        if ($this->nsIP !== null) {
            $aBlock = parse_url($this->sUrl);
            if (!empty($aBlock['query'])) {
                parse_str($aBlock['query'], $aQuery);
            } else {
                $aQuery = array();
            }
            $aOpt[CURLOPT_HTTPHEADER][] = "Host: {$aBlock['host']}";
            $aBlock['host']   = $this->nsIP;

            $aBlock['query'] = http_build_query(array_merge($aQuery, $naParam), '', '&', $iEncType);
        }

        # preset opt https
        if (substr($this->sUrl, 0, 8) === 'https://') {
            if (!isset($aOpt[CURLOPT_SSL_VERIFYHOST])) {
                curl_setopt($rCurl, CURLOPT_SSL_VERIFYHOST, 1);
            }
            if (!isset($aOpt[CURLOPT_SSL_VERIFYPEER])) {
                curl_setopt($rCurl, CURLOPT_SSL_VERIFYPEER, false);
            }
        }

        curl_setopt_array($rCurl, $aOpt);

        $mRS = curl_exec($rCurl);

        if ($mRS === false) {
            return false;
        } else {
            $Req = new HttpResponse();
            //@todo set header body
            return true;
        }
    }

    public static function callGET(
        $sUrl,
        array $naParam = null,
        array $naHeader = null,
        array $naOptKV = null,
        $nsIP = null,
        $iEncType = PHP_QUERY_RFC1738
    ) {
        # preset
        $bReBuild = false;
        $aBlock   = parse_url($sUrl);
        if (!empty($naParam)) {
            if (!empty($aBlock['query'])) {
                parse_str($aBlock['query'], $aQuery);
            } else {
                $aQuery = array();
            }
            $aBlock['query'] = http_build_query(array_merge($aQuery, $naParam), '', '&', $iEncType);
            $bReBuild        = true;
        }
        if ($nsIP !== null) {
            self::preDealWithIP($naHeader, $aBlock, $nsIP);
            $bReBuild = true;
        }
        if ($bReBuild) {
            $sUrl = self::buildUrl($aBlock);
        }
        if ($naHeader !== null) {
            $naOptKV[CURLOPT_HTTPHEADER] = $naHeader;
        }

        return self::call($sUrl, $naOptKV);
    }

    public static function callPOST(
        $sUrl,
        array $naParam = null,
        array $naHeader = null,
        array $naOptKV = null,
        $nsIP = null,
        $iEncType = PHP_QUERY_RFC1738
    ) {
        if ($nsIP !== null) {
            $aBlock = parse_url($sUrl);
            self::preDealWithIP($naHeader, $aBlock, $nsIP);
            $sUrl = self::buildUrl($aBlock);
        }
        if ($naParam !== null) {
            $naOptKV[CURLOPT_POSTFIELDS] = http_build_query($naParam, '', '&', $iEncType);
        }
        if ($naHeader !== null) {
            $naOptKV[CURLOPT_HTTPHEADER] = $naHeader;
        }
        $naOptKV[CURLOPT_POST] = 1;

        return self::call($sUrl, $naOptKV);
    }

    public static function call($sUrl, $naOptKV = null)
    {
        # init
        $rCurl = curl_init($sUrl);
        curl_setopt($rCurl, CURLOPT_RETURNTRANSFER, 1);

        # preset opt https
        if (substr($sUrl, 0, 8) === 'https://') {
            if (!isset($nsOptKV[CURLOPT_SSL_VERIFYHOST])) {
                curl_setopt($rCurl, CURLOPT_SSL_VERIFYHOST, 1);
            }
            if (!isset($nsOptKV[CURLOPT_SSL_VERIFYPEER])) {
                curl_setopt($rCurl, CURLOPT_SSL_VERIFYPEER, false);
            }
        }


        # preset opt timeout
        if (!isset($naOptKV[CURLOPT_TIMEOUT]) && !isset($naOptKV[CURLOPT_TIMEOUT_MS]) && self::$DEFAULT_TIMEOUT_MS !== null) {
            $naOptKV[CURLOPT_TIMEOUT_MS] = self::$DEFAULT_TIMEOUT_MS;
        }
        if (!isset($naOptKV[CURLOPT_CONNECTTIMEOUT]) && !isset($naOptKV[CURLOPT_CONNECTTIMEOUT_MS]) && self::$DEFAULT_CONNECT_TIMEOUT_MS !== null) {
            $naOptKV[CURLOPT_TIMEOUT_MS] = self::$DEFAULT_CONNECT_TIMEOUT_MS;
        }

        # set opt
        if (!empty($naOptKV)) {
            curl_setopt_array($rCurl, $naOptKV);
        }

        # run
        Event::occurEvent(Event_Register::E_CALL_BEFORE, $sUrl, $naOptKV);
        $mRS = curl_exec($rCurl);
        Event::occurEvent(Event_Register::E_CALL_AFTER, $mRS, $sUrl, $naOptKV);

        # return
        return $mRS;
    }

    protected static function preDealWithIP(&$naHeader, &$aBlock, $nsIP)
    {
        $naHeader['host'] = $aBlock['host'];
        $aBlock['host']   = $nsIP;
    }

    public static function buildUrl($aBlock)
    {
        $sScheme   = isset($aBlock['scheme']) ? $aBlock['scheme'] . 'http://' : '';
        $sHost     = isset($aBlock['host']) ? $aBlock['host'] : '';
        $sPort     = isset($aBlock['port']) ? ':' . $aBlock['port'] : '';
        $sUser     = isset($aBlock['user']) ? $aBlock['user'] : '';
        $sPass     = isset($aBlock['pass']) ? ':' . $aBlock['pass'] : '';
        $sPass     = ($sUser || $sPass) ? "$sPass@" : '';
        $sPath     = isset($aBlock['path']) ? $aBlock['path'] : '';
        $sQuery    = isset($aBlock['query']) ? '?' . $aBlock['query'] : '';
        $sFragment = isset($aBlock['fragment']) ? '#' . $aBlock['fragment'] : '';
        return "{$sScheme}{$sUser}{$sPass}{$sHost}{$sPort}{$sPath}{$sQuery}{$sFragment}";
    }
}
