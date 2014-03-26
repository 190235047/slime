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
    /**
     * @param null | \Slime\Component\Context\Context $Context
     */
    public function __construct($Context = null)
    {
        $this->Context = $Context;
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
        foreach ($aRule as $siK => $aV) {
            if (!is_array($aV) || !isset($aV[0]) || !isset($aV[1]) || !isset($aV[2])) {
                throw new \DomainException("[ROUTE] : Rule value expect a array [0:mixed, 1:controller_pre, 2:action_pre]");
            }
            $mV             = $aV[0];
            $sControllerPre = $aV[1];
            $sActionPre     = $aV[2];
            if ($this->Context !== null) {
                $this->Context->register('sControllerPre', $sControllerPre);
                $this->Context->register('sActionPre', $sActionPre);
            }
            $HitMode->setAsCommon();
            if (is_string($siK) && preg_match($siK, $REQ->getRequestURI(), $aMatched)) {
                if (is_array($mV) && !isset($mV[0])) {
                    // key:   #^(book|article)/(\d+?)/(status)/(\d+?)$#
                    // value: array('object' => $1, 'method' => $3, 'param' => array('id' => $2, 'status' => $4))
                    // value: array('func' => $1_$3, 'param' => array('id' => $2, 'status' => $4), '__interceptor__'=>HitMode::M_MAIN_STOP)
                    $mResult = new CallBack($sControllerPre, $sActionPre);
                    if (!empty($mV['__interceptor__'])) {
                        $HitMode->setMode($mV['__interceptor__']);
                    }
                    $aSearch = $aReplace = array();
                    foreach ($aMatched as $iK => $sV) {
                        $aSearch[$iK]  = '$' . $iK;
                        $aReplace[$iK] = $sV;
                    }
                    $mV = self::replaceRecursive($mV, $aSearch, $aReplace);
                    if (isset($mV['object']) && isset($mV['method'])) {
                        $mResult->setCBObject($mV['object'], $mV['method']);
                    } elseif (isset($mV['class']) && $mV['method']) {
                        $mResult->setCBClass($mV['class'], $mV['method']);
                    } elseif (isset($mV['func'])) {
                        $mResult->setCBFunc($mV['func']);
                    } else {
                        throw new \DomainException('[ROUTE] : Route rule error. one of [object, class, func] must be used for array key');
                    }
                    if (isset($mV['param'])) {
                        $mResult->setParam($mV['param']);
                    }
                } else {
                    // key:   #^(book|article)/(\d+?)/(status)/(\d+?)$#
                    // value: function($REQ, $RES, $aMatched, $HitMode, $sControllerPre){}
                    // value: array(cbClass/cbObj, cbMethod)
                    // value: cbFunction
                    $mResult = call_user_func_array(
                        $mV,
                        array(
                            $REQ,
                            $RES,
                            $aMatched,
                            $HitMode,
                            $sControllerPre
                        )
                    );
                }
            } else {
                $mResult = call_user_func_array(
                    $mV,
                    array(
                        $REQ,
                        $RES,
                        $HitMode,
                        $sControllerPre
                    )
                );
            }

            if ($mResult instanceof CallBack) {
                $aCallBack[] = $mResult;
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
     * generate from cli input [/your_php_bin/php /your_project/index.php class.method|func json_str_for_param
     *
     * @return array [0=>callable, 1=>params] || []
     */

    /**
     * @param array $aArg
     * @param array $aRule
     * @param bool  $bHitMain
     *
     * @throws \DomainException
     * @return \Slime\Component\Route\CallBack[]
     */
    public function generateFromCli(array $aArg, array $aRule, &$bHitMain)
    {
        $bHitMain  = false;
        $aCallBack = array();
        $HitMode   = new HitMode();
        foreach ($aRule as $aV) {
            if (!is_array($aV) || !isset($aV[0]) || !isset($aV[1]) || !isset($aV[2])) {
                throw new \DomainException("[ROUTE] : Rule value expect a array [0:mixed, 1:controller_pre, 2:action_pre]");
            }
            $mV             = $aV[0];
            $sControllerPre = $aV[1];
            $sActionPre     = $aV[2];
            if ($this->Context !== null) {
                $this->Context->register('sControllerPre', $sControllerPre);
                $this->Context->register('sActionPre', $sActionPre);
            }
            $HitMode->setAsCommon();
            $mResult = call_user_func_array($mV, array($aArg, $HitMode, $sControllerPre));
            if ($mResult instanceof CallBack) {
                $aCallBack[] = $mResult;
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
