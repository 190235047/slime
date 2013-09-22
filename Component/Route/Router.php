<?php
namespace SlimeFramework\Component\Route;

use SlimeFramework\Component\Http;
use Psr\Log\LoggerInterface;

/**
 * Class Route
 * @package Slime
 * @author smallslime@gmail.com
 * @version 1.0
 */
class Router
{
    /**
     * @param string $sAppNS
     * @param LoggerInterface $Log
     */
    public function __construct($sAppNS, LoggerInterface $Log)
    {
        $this->sAppNS = $sAppNS;
        $this->Log    = $Log;
    }

    /**
     * @param Http\Request $HttpRequest
     * @param $aRule
     * @return \SlimeFramework\Component\Route\CallBack
     */
    public function generateFromHttp(Http\Request $HttpRequest, $aRule)
    {
        $CallBack = new CallBack($this->sAppNS, $this->Log);
        foreach ($aRule as $sK => $mV) {
            $bContinue = false;
            if (is_string($sK) && preg_match($sK, $HttpRequest->getRequestURI(), $aMatched)) {
                if (is_callable($mV)) {
                    // key:   #^(book|article)/(\d+?)/(status)/(\d+?)$#
                    // value: function($a, $b, $c, $d){}
                    array_unshift($aMatched, $CallBack);
                    $bContinue = call_user_func_array($mV, $aMatched) !== false;
                } elseif (is_array($mV)) {
                    // key:   #^(book|article)/(\d+?)/(status)/(\d+?)$#
                    // value: array('object' => $1, 'method' => $3, 'param' => array('id' => $2, 'status' => $4))
                    // value: array('func' => $1_$3, 'param' => array('id' => $2, 'status' => $4), '_continue'=>false)
                    if (isset($mV['_continue'])) {
                        $bContinue = $mV['_continue'] !== false;
                        unset($mV['_continue']);
                    }
                    $mV = $this->replaceRecursive($mV, $aMatched);
                    if (isset($mV['object'])) {
                        $CallBack->setCBObject($mV['object'], $mV['method']);
                    } elseif (isset($mV['class'])) {
                        $CallBack->setCBClass($mV['class'], $mV['method']);
                    } elseif (isset($mV['func'])) {
                        $CallBack->setCBFunc($mV['func']);
                    } else {
                        $this->Log->error('Route rule error. one of [object, class, func] must be used for array key');
                        exit(1);
                    }
                    if (isset($mV['param'])) {
                        $CallBack->setParam($mV['param']);
                    }
                }
            } elseif (is_int($sK)) {
                if (is_string($mV)) {
                    if ($mV[0] == '@') {
                        $mV = __NAMESPACE__ . '\\Mode_' . substr($mV, 1);
                    }
                    $Mode = new $mV();
                    if (!$Mode instanceof IMode) {
                        $this->Log->error('Route rule error. Your own route mode must impl IMode');
                        exit(1);
                    }
                    $bContinue = $Mode->runHttp($HttpRequest, $CallBack);
                    // value: @routeSlimeStyle
                } elseif (is_callable($mV)) {
                    // value: function(){}
                    $bContinue = call_user_func($mV, $CallBack) !== false;
                }
            }
            if ($bContinue === false) {
                break;
            }
        }

        return $CallBack;
    }

    /**
     * generate from cli input [/your_php_bin/php /your_project/index.php class.method|func json_str
     * @return array [0=>callable, 1=>params] || []
     */

    /**
     * @param array $aArg
     * @param array $aRule
     * @return \SlimeFramework\Component\Route\CallBack
     */
    public function generateFromCli(array $aArg, array $aRule)
    {
        $CallBack = new CallBack($this->sAppNS, $this->Log);
        foreach ($aRule as $mV) {
            $bContinue = false;
            if (is_string($mV)) {
                if ($mV[0] == '@') {
                    $mV = __NAMESPACE__ . '\\Mode_' . substr($mV, 1);
                }
                $Mode = new $mV();
                if (!$Mode instanceof IMode) {
                    $this->Log->error('Route rule error. Your own route mode must impl IMode');
                    exit(1);
                }
                $bContinue = $Mode->runCli($aArg, $CallBack);
                // value: @routeSlimeStyle
            } elseif (is_callable($mV)) {
                // value: function(){}
                $bContinue = call_user_func($mV, $CallBack) !== false;
            }
            if ($bContinue === false) {
                break;
            }
        }
        return $CallBack;
    }

    private function replaceRecursive($aArr, $aMatched)
    {
        foreach ($aArr as $mK => $mRow) {
            $aArr[$mK] = is_array($mRow) ?
                $this->replaceRecursive($mRow, $aMatched) :
                (
                is_string($mRow) ?
                    str_replace($mRow, $aMatched, $mRow) :
                    $mRow
                );
        }
        return $aArr;
    }
}
