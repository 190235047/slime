<?php
namespace Slime\Component\Route;

/**
 * Class Route
 *
 * @package Slime\Component\Http
 * @author  smallslime@gmail.com
 */
class Router
{
    public function __construct($sAppNS, $sControllerPre)
    {
        $this->sAppNS         = $sAppNS;
        $this->sControllerPre = $sControllerPre;
    }

    /**
     * @param \Slime\Component\Http\HttpRequest  $REQ
     * @param \Slime\Component\Http\HttpResponse $RES
     * @param array                              $aRule
     * @param bool                               $bHitMain
     *
     * @throws \DomainException
     * @return \Slime\Component\Route\CallBack[]
     */
    public function generateFromHttp($REQ, $RES, $aRule, &$bHitMain)
    {
        $bHitMain  = false;
        $aCallBack = array();
        $HitMode   = new HitMode();
        foreach ($aRule as $sK => $mV) {
            $HitMode->setAsCommon();
            if (is_string($sK)) {
                if (preg_match($sK, $REQ->getRequestURI(), $aMatched)) {
                    if (is_callable($mV)) {
                        // key:   #^(book|article)/(\d+?)/(status)/(\d+?)$#
                        // value: function($REQ, $RES, $aMatched, $Continue, $Hit, $sAppNS, $sCtrlPre){}
                        $mResult = call_user_func_array(
                            $mV,
                            array(
                                $REQ,
                                $RES,
                                $aMatched,
                                $HitMode,
                                $this->sAppNS,
                                $this->sControllerPre
                            )
                        );
                        if ($mResult instanceof CallBack) {
                            $aCallBack[] = $mResult;
                        }
                    } elseif (is_array($mV)) {
                        $CallBack = new CallBack($this->sAppNS);
                        // key:   #^(book|article)/(\d+?)/(status)/(\d+?)$#
                        // value: array('object' => $1, 'method' => $3, 'param' => array('id' => $2, 'status' => $4))
                        // value: array('func' => $1_$3, 'param' => array('id' => $2, 'status' => $4), '_continue'=>false)
                        if (!empty($mV['__HitMode__'])) {
                            $HitMode->setMode($mV['__HitMode__']);
                        }
                        $aSearch = $aReplace = array();
                        foreach ($aMatched as $iK => $sV) {
                            $aSearch[$iK]  = '$' . $iK;
                            $aReplace[$iK] = $sV;
                        }
                        $mV = self::replaceRecursive($mV, $aSearch, $aReplace);
                        if (isset($mV['object']) && isset($mV['method'])) {
                            $CallBack->setCBObject($mV['object'], $mV['method']);
                        } elseif (isset($mV['class']) && $mV['method']) {
                            $CallBack->setCBClass($mV['class'], $mV['method']);
                        } elseif (isset($mV['func'])) {
                            $CallBack->setCBFunc($mV['func']);
                        } else {
                            throw new \DomainException('[ROUTE] : Route rule error. one of [object, class, func] must be used for array key');
                        }
                        if (isset($mV['param'])) {
                            $CallBack->setParam($mV['param']);
                        }
                        $aCallBack[] = $CallBack;
                    }
                }
            } elseif (is_int($sK)) {
                $mResult = call_user_func_array(
                    $mV,
                    array(
                        $REQ,
                        $RES,
                        $HitMode,
                        $this->sAppNS,
                        $this->sControllerPre
                    )
                );
                if ($mResult instanceof CallBack) {
                    $aCallBack[] = $mResult;
                }
            }

            if ($bHitMain === false && $HitMode->isMainLogic()) {
                $bHitMain = true;
            }
            if (!$HitMode->ifNeedGoOn()) {
                break;
            }
        }
        return $aCallBack;
    }

    /**
     * generate from cli input [/your_php_bin/php /your_project/index.php class.method|func json_str
     *
     * @return array [0=>callable, 1=>params] || []
     */

    /**
     * @param array $aArg
     * @param array $aRule
     * @param bool  $bHitMain
     *
     * @return \Slime\Component\Route\CallBack[]
     */
    public function generateFromCli(array $aArg, array $aRule, &$bHitMain)
    {
        $bHitMain    = false;
        $aCallBack   = array();
        $Interceptor = new HitMode();
        foreach ($aRule as $mV) {
            $Interceptor->setAsCommon();
            $mResult = call_user_func_array($mV, array($aArg, $Interceptor, $this->sAppNS, $this->sControllerPre));
            if ($mResult instanceof CallBack) {
                $aCallBack[] = $mResult;
            }
            if ($bHitMain === false && $Interceptor->isMainLogic()) {
                $bHitMain = true;
            }
            if (!$Interceptor->ifNeedGoOn()) {
                break;
            }
        }
        return $aCallBack;
    }

    /**
     * @param array $aArr
     * @param array $aSearch
     * @param array $aReplace
     *
     * @return mixed
     */
    public static function replaceRecursive($aArr, $aSearch, $aReplace)
    {
        foreach ($aArr as $mK => $mRow) {
            $aArr[$mK] = is_array($mRow) ?
                self::replaceRecursive($mRow, $aSearch, $aReplace) :
                str_replace($aSearch, $aReplace, $mRow);
        }
        return $aArr;
    }
}
