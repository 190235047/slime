<?php
namespace Slime\Component\Http\Call;

use Slime\Component\Context\Event;

if (!extension_loaded('curl')) {
    throw new \Exception('[EXT] Extension curl is not loaded');
}

class Engine
{
    protected $aThisTimeOptKV = array();

    public function __construct($iConnectTimeOutMS = 3000, $iTimeOutMS = 3000, array $aOptKV = array())
    {
        $this->iDefaultConnectTimeOutMS = 3000;
        $this->iDefaultTimeOutMS        = 3000;
        $this->aOptKVDefault            = $aOptKV;
    }

    public function setResultOnlyBodyThisTime()
    {
        $this->aThisTimeOptKV[CURLOPT_HEADER] = false;
        $this->aThisTimeOptKV[CURLOPT_NOBODY] = false;
    }

    public function setResultOnlyHeaderThisTime()
    {
        $this->aThisTimeOptKV[CURLOPT_HEADER] = true;
        $this->aThisTimeOptKV[CURLOPT_NOBODY] = true;
    }

    public function setResultHeaderAndBodyThisTime()
    {
        $this->aThisTimeOptKV[CURLOPT_HEADER] = true;
        $this->aThisTimeOptKV[CURLOPT_NOBODY] = false;
    }

    public function callGET(
        $sUrl,
        array $naParam = null,
        array $naHeader = null,
        $nsIP = null,
        array $aOptKV = array(),
        $iEncType = PHP_QUERY_RFC1738
    ) {
        # preset
        $bReBuild = false;
        $aBlock   = parse_url($sUrl);
        if (!empty($naParam)) {
            if (!empty($aBlock['query'])) {
                parse_str($aBlock['query'], $aQuery);
                $aBlock['query'] = http_build_query(array_merge($aQuery, $naParam), '', '', $iEncType);
                $bReBuild        = true;
            }
        }
        if ($nsIP !== null) {
            self::preDealWithIP($naHeader, $aBlock, $nsIP);
            $bReBuild = true;
        }
        if ($bReBuild) {
            $sUrl = self::buildUrl($aBlock);
        }
        if ($naHeader !== null) {
            $aOptKV[CURLOPT_HTTPHEADER] = $naHeader;
        }

        return $this->call($sUrl, $aOptKV);
    }

    public function callPOST(
        $sUrl,
        array $naParam = null,
        array $naHeader = null,
        $nsIP = null,
        array $aOptKV = array(),
        $iEncType = PHP_QUERY_RFC1738
    ) {
        if ($nsIP !== null) {
            $aBlock = parse_url($sUrl);
            self::preDealWithIP($naHeader, $aBlock, $nsIP);
            $sUrl = self::buildUrl($aBlock);
        }
        if ($naParam !== null) {
            $aOptKV[CURLOPT_POSTFIELDS] = http_build_query($naParam, '', '', $iEncType);
        }
        if ($naHeader !== null) {
            $aOptKV[CURLOPT_HTTPHEADER] = $naHeader;
        }
        $aOptKV[CURLOPT_POST] = 1;

        return self::call($sUrl, $aOptKV);
    }

    public function callPOSTWithFile()
    {
        //@todo;
    }

    /**
     * @param  string      $sUrl
     * @param  array $aOptKV
     *
     * @return array [0:header(string | false), 1:body(string | false)]
     */
    public function call($sUrl, array $aOptKV = array())
    {
        # init
        $rCurl = curl_init($sUrl);
        curl_setopt($rCurl, CURLOPT_RETURNTRANSFER, 1);

        # preset opt header
        if (!empty($aHeader)) {
            $aTidyHeader = array();
            foreach ($aHeader as $sK => $sV) {
                $aTidyHeader[] = "$sK: $sV";
            }
            curl_setopt($rCurl, CURLOPT_HTTPHEADER, $aTidyHeader);
        }

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
        if (!isset($aOptKV[CURLOPT_TIMEOUT]) && !isset($aOptKV[CURLOPT_TIMEOUT_MS])) {
            $aOptKV[CURLOPT_TIMEOUT_MS] = $this->iDefaultTimeOutMS;
        }
        if (!isset($aOptKV[CURLOPT_CONNECTTIMEOUT]) && !isset($aOptKV[CURLOPT_CONNECTTIMEOUT_MS])) {
            $aOptKV[CURLOPT_TIMEOUT_MS] = $this->iDefaultConnectTimeOutMS;
        }

        # set opt
        if (!empty($this->aOptKVDefault) || !empty($this->aThisTimeOptKV) || !empty($aOptKV)) {
            $aOptKV = $aOptKV + $this->aThisTimeOptKV + $this->aOptKVDefault;
            curl_setopt_array($rCurl, $aOptKV);
        }

        # run
        Event::occurEvent(Event_Register::E_CALL_BEFORE, $sUrl, $aOptKV);
        $mRS = curl_exec($rCurl);
        Event::occurEvent(Event_Register::E_CALL_AFTER, $mRS, $sUrl, $aOptKV);

        # return
        $mHeader = $mBody = false;
        if ($mRS !== false) {
            if (empty($aOptKV[CURLOPT_HEADER]) && empty($aOptKV[CURLOPT_NOBODY])) {
                //no header has body
                $mBody = (string)$mRS;
            } elseif (empty($aOptKV[CURLOPT_HEADER])) {
                //no header no body
            } elseif (empty($aOptKV[CURLOPT_NOBODY])) {
                // has header has body
                $aArr = explode("\r\n\r\n", $mRS, 2);
                $mHeader = (string)$aArr[0];
                $mBody   = isset($aArr[1]) ? (string)$aArr[1] : '';
            } else {
                $mHeader = (string)$mRS;
            }
        }

        return array($mHeader, $mBody);
    }

    protected static function preDealWithIP(&$naHeader, &$aBlock, $nsIP)
    {
        $naHeader['host'] = $aBlock['host'];
        $aBlock['host']   = $nsIP;
    }

    public static function buildUrl($aBlock)
    {
        $sScheme   = isset($aBlock['scheme']) ? $aBlock['scheme'] . '://' : '';
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