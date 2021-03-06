<?php
namespace Slime\Component\Http;

use Slime\Component\Support\ABS_Container;
use Slime\Component\Support\Url;

if (!extension_loaded('curl')) {
    throw new \Exception('[EXT] Extension curl is not loaded');
}

/**
 * Class Call
 *
 * @package Slime\Component\Http
 *
 * @method Call head() head()
 * @method Call get() get()
 * @method Call post() post()
 * @method Call put() put()
 * @method Call delete() delete()
 */
class Call
{
    const EV_EXEC_BEFORE = 'slime.component.http.http_call.exec_before';
    const EV_EXEC_AFTER = 'slime.component.http.http_call.exec_after';

    protected $nsUrl;
    protected $iConnTimeout;
    protected $iTimeout;
    protected $nsIP = null;
    protected $niPort = null;
    protected $aPostData = array();
    protected $aFileMap = array();
    protected $aOpt = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_NOBODY         => false
    );
    protected $aHeader = array();
    protected $mRS;

    /**
     * @param int                               $iConnTimeoutMS
     * @param int                               $iTimeoutMS
     * @param null|\Slime\Component\Event\Event $nEV
     */
    public function __construct($iConnTimeoutMS = 3000, $iTimeoutMS = 3000, $nEV = null)
    {
        $this->setTimeOut($iConnTimeoutMS, $iTimeoutMS);
        $this->nEV = $nEV;
    }

    public function __get($sVar)
    {
        return $this->$sVar;
    }

    /**
     * @param string $sUrl
     *
     * @return $this
     */
    public function setUrl($sUrl)
    {
        $this->nsUrl = $sUrl;

        return $this;
    }

    /**
     * @param bool $bGetHeader
     * @param bool $bGetBody
     */
    public function setFetchMethod($bGetHeader = false, $bGetBody = true)
    {
        $this->aOpt[CURLOPT_HEADER] = $bGetHeader;
        $this->aOpt[CURLOPT_NOBODY] = !$bGetBody;
    }


    /**
     * @param int $iConnTimeoutMS
     * @param int $iTimeoutMS
     *
     * @return $this
     */
    public function setTimeOut($iConnTimeoutMS, $iTimeoutMS)
    {
        $this->iConnTimeout = $iConnTimeoutMS;
        $this->iTimeout     = $iTimeoutMS;

        return $this;
    }

    /**
     * @param string   $sIP
     * @param null|int $niPort
     */
    public function setRealHost($sIP, $niPort = null)
    {
        $this->nsIP   = $sIP;
        $this->niPort = $niPort;
    }


    /**
     * @param array $aKV
     *
     * @return $this
     */
    public function setPostData(array $aKV)
    {
        $this->aPostData = empty($this->aPostData) ? $aKV : array_merge($this->aPostData, $aKV);

        return $this;
    }


    /**
     * @param array $aKVName2File
     *
     * @return $this
     */
    public function setFileData(array $aKVName2File)
    {
        $this->aFileMap = empty($this->aFileMap) ? $aKVName2File : array_merge($this->aFileMap, $aKVName2File);

        return $this;
    }


    /**
     * @param array $aOpt
     */
    public function setOpt(array $aOpt)
    {
        $this->aOpt = empty($this->aOpt) ? $aOpt : array_merge($aOpt, $this->aOpt);
    }


    /**
     * @param array $aKV
     */
    public function setHeaders(array $aKV)
    {
        $this->aHeader = empty($this->aHeader) ? $aKV : array_merge($this->aHeader, $aKV);
    }

    /**
     * @param string $sMethod
     * @param array  $aArgv
     *
     * @return Call
     */
    public function call($sMethod, $aArgv = array())
    {
        return $this->__call($sMethod, $aArgv);
    }

    public function __call($sMethodName, $aArgv)
    {
        $aOpt = $this->aOpt;

        # url
        $sUrl = $this->nsUrl;
        if ($sUrl === null) {
            throw new \RuntimeException("[HTTP] ; Please call setUrl first");
        }
        if ($this->nsIP !== null) {
            $aBlock                = parse_url($sUrl);
            $this->aHeader['Host'] = isset($aBlock['port']) ? "{$aBlock['host']}:{$aBlock['port']}" : "{$aBlock['host']}";
            $aBlock['host']        = $this->nsIP;
            if ($this->niPort !== null) {
                $aBlock['port'] = $this->niPort;
            }

            $sUrl = Url::build($aBlock);
        }
        $rCurl = curl_init($sUrl);

        # preset opt https
        if (substr($this->nsUrl, 0, 8) === 'https://') {
            if (!isset($aOpt[CURLOPT_SSL_VERIFYHOST])) {
                $aOpt[CURLOPT_SSL_VERIFYHOST] = 1;
            }
            if (!isset($aOpt[CURLOPT_SSL_VERIFYPEER])) {
                $aOpt[CURLOPT_SSL_VERIFYPEER] = false;
            }
        }

        # header
        $aHeader = array();
        foreach ($this->aHeader as $sK => $sV) {
            $aHeader[] = "$sK: $sV";
        }
        if (empty($aOpt[CURLOPT_HTTPHEADER])) {
            $aOpt[CURLOPT_HTTPHEADER] = $aHeader;
        } else {
            $aOpt[CURLOPT_HTTPHEADER] = array_merge($aOpt[CURLOPT_HTTPHEADER], $aHeader);
        }

        switch (strtoupper($sMethodName)) {
            case 'GET':
                break;
            case 'POST':
                $aOpt[CURLOPT_POST] = 1;
                if (!empty($this->aFileMap)) {
                    $aData = array_merge($this->aPostData, $this->aFileMap);
                } else {
                    $aData = empty($this->aPostData) ? '' : http_build_query($this->aPostData);
                }
                $aOpt[CURLOPT_POSTFIELDS] = $aData;
                break;
            default:
                $aOpt[CURLOPT_CUSTOMREQUEST] = $sMethodName;
                break;
        }

        curl_setopt_array($rCurl, $aOpt);

        if ($this->nEV) {
            $Local  = new \ArrayObject();
            $aParam = array($this, $sMethodName, $aArgv, $Local);
            $this->nEV->fire(self::EV_EXEC_BEFORE, $aParam);
            if (!isset($Local['__RESULT__'])) {
                $Local['__RESULT__'] = curl_exec($rCurl);
            }
            $this->nEV->fire(self::EV_EXEC_AFTER, $aParam);
            $this->mRS = $Local['__RESULT__'];
        } else {
            $this->mRS = curl_exec($rCurl);
        }


        return $this;
    }

    /**
     * @return string
     */
    public function asString()
    {
        return $this->mRS;
    }

    /*
    public function asOBJ()
    {
        if ($this->mRS === false) {
            throw new HttpCallFailedException();
        }
        $aArr = explode("\r\n\r\n", $this->mRS, 2);
        if (count($aArr) !== 2) {
            throw new \RuntimeException("[HTTP] : Data format error");
        }
        $aHeader     = explode("\r\n", $aArr[0]);
        $bFirst      = false;
        $aTidyHeader = array();
        $niCode      = null;
        $nsProtocol  = null;
        foreach ($aHeader as $sRow) {
            if (!$bFirst) {
                if (trim($sRow) === '') {
                    continue;
                }
                $aBlock     = explode(' ', $sRow, 3);
                $niCode     = (int)$aBlock[1];
                $nsProtocol = $aBlock[0];
                $bFirst     = true;
            } else {
                $aRow = explode(':', $sRow, 2);
                if (count($aRow) !== 2) {
                    trigger_error("[HTTP] Header format error[{$sRow}]", E_WARNING);
                    continue;
                }
                $aTidyHeader[trim($aRow[0])] = ltrim($aRow[1]);
            }
        }
        //@todo
    }
    */
}

class HttpCallFailedException extends \LogicException
{
}
