<?php
namespace Slime\Component\Route;

use Slime\Component\Http;

/**
 * Class Route
 *
 * @package Slime
 * @author  smallslime@gmail.com
 * @version 1.0
 */
class Router
{
    /**
     * @param string $sAppNS
     */
    public function __construct($sAppNS)
    {
        $this->sAppNS = $sAppNS;
    }

    /**
     * @param Http\HttpRequest  $HttpRequest
     * @param Http\HttpResponse $HttpResponse
     * @param array             $aRule
     *
     * @throws \Exception
     * @return \Slime\Component\Route\CallBack[]
     */
    public function generateFromHttp(Http\HttpRequest $HttpRequest, Http\HttpResponse $HttpResponse, $aRule)
    {
        $aCallBack = array();
        foreach ($aRule as $sK => $mV) {
            $bContinue = false;
            if (is_string($sK)) {
                if (!preg_match($sK, $HttpRequest->getRequestURI(), $aMatched)) {
                    continue;
                } else {
                    if (is_callable($mV)) {
                        // key:   #^(book|article)/(\d+?)/(status)/(\d+?)$#
                        // value: function($a, $b, $c, $d){}
                        $mResult = call_user_func_array(
                            $mV,
                            array(
                                $HttpRequest,
                                $HttpResponse,
                                $aMatched,
                                &$bContinue,
                                $this->sAppNS
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
                        if (isset($mV['_continue'])) {
                            $bContinue = $mV['_continue'] !== false;
                            unset($mV['_continue']);
                        }
                        $aSearch = $aReplace = array();
                        foreach ($aMatched as $iK => $sV) {
                            $aSearch[$iK] = '$' . $iK;
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
                            throw new \Exception('Route rule error. one of [object, class, func] must be used for array key');
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
                        $HttpRequest,
                        $HttpResponse,
                        &$bContinue,
                        $this->sAppNS
                    )
                );
                if ($mResult instanceof CallBack) {
                    $aCallBack[] = $mResult;
                }
            }
            if ($bContinue === false) {
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
     *
     * @return \Slime\Component\Route\CallBack[]
     */
    public function generateFromCli(array $aArg, array $aRule)
    {
        $aCallBack = array();
        foreach ($aRule as $mV) {
            $bContinue = false;
            $mResult = call_user_func_array($mV, array($aArg, &$bContinue, $this->sAppNS));
            if ($mResult instanceof CallBack) {
                $aCallBack[] = $mResult;
            }
            if ($bContinue === false) {
                break;
            }
        }
        return $aCallBack;
    }

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
