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
    const MODE_CB = 0;
    const MODE_EASY = 1;

    protected $aConfig;

    public function set($m_sRE_iIndex, $aConf)
    {
        $this->aConfig[is_int($m_sRE_iIndex) ? '' : $m_sRE_iIndex][] = $aConf;

        return $this;
    }

    public function setMulti($aArr)
    {
        foreach ($aArr as $m_sRE_iIndex => $aConf) {
            $this->aConfig[is_int($m_sRE_iIndex) ? '' : $m_sRE_iIndex][] = $aConf;
        }

        return $this;
    }

    /**
     * @param \Slime\Component\Http\REQ|array      $REQ
     * @param \Slime\Component\Http\RESP|null      $RESP
     * @param \Slime\Component\Log\LoggerInterface $Log
     * @param \Slime\Component\Support\Context     $CTX
     */
    public function run($REQ, $RESP, $Log, $CTX)
    {
        $aParamDefault = $RESP === null ? array($REQ, $Log, $CTX) : array($REQ, $RESP, $Log, $CTX);
        foreach ($this->aConfig as $sRE => $aArr) {
            # param to callback
            if ($sRE === '') {
                $aParam = $aParamDefault;
            } else {
                if (!preg_match($sRE, $REQ->getRequestURI(), $aMatch)) {
                    continue;
                }
                array_shift($aMatch);
                $aParam = array_merge(array($aMatch), $aParamDefault);
            }

            foreach ($aArr as $mRow) {
                # filter
                if (isset($mRow['filter']) && !call_user_func_array($mRow['filter'], $aParam)) {
                    continue;
                }

                # callback by rule
                if (empty($mRow['easy_mode'])) {
                    if (isset($mRow['setting'])) {
                        $aParam[] = $mRow['setting'];
                    }
                    if (!call_user_func_array($mRow['callback'], $aParam)) {
                        break;
                    }
                    continue;
                }

                # callback by RE
                if (is_array($aParam[0])) { // has done preg
                    $aSearch = $aReplace = array();
                    foreach ($aParam as $iK => $sV) {
                        if (!is_string($sV)) {
                            continue;
                        }
                        $aSearch[$iK]  = '$' . $iK;
                        $aReplace[$iK] = $sV;
                    }
                    $mCBItem   = self::replaceRec($mRow, $aSearch, $aReplace);
                    $aParam[0] = $mCBItem['params'];
                }
                // callback direct
                if (isset($mCBItem['callback'])) {
                    if (!call_user_func_array($mCBItem['callback'], $aParam)) {
                        break;
                    }
                    continue;
                }
                // callback as object
                if (!Mode::objCall($mCBItem['controller'], $mCBItem['action'], $aParam)) {
                    break;
                }
            }
        }
    }

    public static function replaceRec($aArr, $aSearch, $aReplace)
    {
        foreach ($aArr as $mK => $mRow) {
            if (!is_array($mRow) || !is_string($mRow)) {
                continue;
            }
            $aArr[$mK] = is_array($mRow) ?
                self::replaceRec($mRow, $aSearch, $aReplace) :
                str_replace($aSearch, $aReplace, $mRow);
        }
        return $aArr;
    }
}

class RouteException extends \LogicException
{
    public function __construct($sMessage, $iCode = 0, $E = null)
    {
        parent::__construct($sMessage, $iCode, $E);
    }
}