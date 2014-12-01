<?php
namespace Slime\Component\Route;

/**
 * Class Route
 *
 * @package Slime\Component\Route
 * @author  smallslime@gmail.com
 */
class Router
{
    protected $aConfig = array();

    public function addConfig(array $aConfig)
    {
        $this->aConfig = array_merge($this->aConfig, $aConfig);

        return $this;
    }

    public function addGET($sRE, $mCB)
    {
        $this->aConfig[] = array('__FILTERS__' => '@isGET', '__RE__' => $sRE, '__CB__' => $mCB);

        return $this;
    }

    public function addPOST($sRE, $mCB)
    {
        $this->aConfig[] = array('__FILTERS__' => '@isPOST', '__RE__' => $sRE, '__CB__' => $mCB);

        return $this;
    }

    public function add($sRE, $mCB)
    {
        $this->aConfig[] = array('__RE__' => $sRE, '__CB__' => $mCB);

        return $this;
    }

    /**
     * @param \Slime\Component\Http\REQ   $REQ
     * @param \Slime\Component\Http\RESP  $RESP
     * @param \Slime\Component\Support\Context     $CTX
     */
    public function runHttp($REQ, $RESP, $CTX)
    {
        $aDefaultParam = array($REQ, $RESP, $CTX);
        $sUrl = $REQ->getUrl();

        foreach ($this->aConfig as $aArr) {
            $aParam = $aDefaultParam;

            if (isset($aArr['__RE__'])) {
                if (!preg_match($aArr['__RE__'], $sUrl, $aMatch)) {
                    continue;
                }
                array_shift($aMatch);
                $aParam = array_merge($aParam, $aMatch);
            }

            if (isset($aArr['__PARAM__'])) {
                $aParam[] = $aArr['__PARAM__'];
            }

            if (isset($aArr['__FILTERS__'])) {
                foreach ($aArr['__FILTERS__'] as $mFilter) {
                    if (is_string($mFilter) && $mFilter[0]==='@') {
                        $mFilter = array('\\Slime\\Component\\Route\\Filter', substr($mFilter, 1));
                    }
                    if (!call_user_func_array($mFilter, $aParam)) {
                        continue 2;
                    }
                }
            }

            if (!call_user_func_array($aArr['__CB__'], $aParam)) {
                break;
            }
        }
    }

    public function runCli($aArgv, $CTX)
    {
        foreach ($this->aConfig as $aArr) {
            if (!call_user_func($aArr['__CB__'], $aArgv, $CTX)) {
                break;
            }
        }
    }
}

class RouteException extends \LogicException
{
    public function __construct($sMessage, $iCode = 0, $E = null)
    {
        parent::__construct($sMessage, $iCode, $E);
    }
}