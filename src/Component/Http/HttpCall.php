<?php
namespace Slime\Component\Http;

use Slime\Component\Context\Event;

if (!extension_loaded('curl')) {
    throw new \Exception('[EXT] Extension curl is not loaded');
}

class HttpCall
{
    public static $DEFAULT_TIMEOUT_MS = 3000;
    public static $DEFAULT_CONNECT_TIMEOUT_MS = 3000;

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
            $naOptKV[CURLOPT_POSTFIELDS] = http_build_query($naParam, '', '', $iEncType);
        }
        if ($naHeader !== null) {
            $naOptKV[CURLOPT_HTTPHEADER] = $naHeader;
        }
        $naOptKV[CURLOPT_POST] = 1;

        return self::call($sUrl, $naOptKV);
    }

    public static function callPOSTWithFile()
    {
        //@todo;
    }

    public static function call($sUrl, $naOptKV = null)
    {
        # init
        $rCurl = curl_init($sUrl);
        curl_setopt($rCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($rCurl, CURLOPT_HEADER, 1);

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
        if (!isset($naOptKV[CURLOPT_TIMEOUT]) && !isset($naOptKV[CURLOPT_TIMEOUT_MS]) && self::$DEFAULT_TIMEOUT_MS!==null) {
            $naOptKV[CURLOPT_TIMEOUT_MS] = self::$DEFAULT_TIMEOUT_MS;
        }
        if (!isset($naOptKV[CURLOPT_CONNECTTIMEOUT]) && !isset($naOptKV[CURLOPT_CONNECTTIMEOUT_MS]) && self::$DEFAULT_CONNECT_TIMEOUT_MS!==null) {
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