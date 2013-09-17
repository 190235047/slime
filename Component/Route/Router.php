<?php
namespace SlimeFramework\Component\Route;

/**
 * Class Route
 * @package Slime
 * @author smallslime@gmail.com
 * @version 1.0
 */
class Router
{
    /**
     * @var array
     */
    protected $aRule;

    /** @var string */
    protected $sAppNS;

    /**
     * @param array $aConfig
     * @param array $aRule
     * @param string $sAppNS
     */
    public function __construct($aConfig, $aRule, $sAppNS)
    {
        $this->aConfig  = $aConfig;
        $this->aRule    = $aRule;
        $this->sAppNS   = $sAppNS;
    }

    /**
     * generate a callback, if failed return false
     * @return mixed false|array [0=>callable, 1=>params]
     */
    public function run()
    {
        return PHP_SAPI == 'cli' ?
            $this->generateFromCli() :
            $this->generateFromHttp();
    }

    /**
     * generate from http
     * @return array [0=>callable, 1=>params] || []
     */
    private function generateFromHttp()
    {
        $RequestURI = $_SERVER['REQUEST_URI'];
        $aResult    = array();
        foreach ($this->aRule as $sK => $mV) {
            $mRS = false;
            if (is_string($sK)) {
                if (is_callable($mV) && preg_match($sK, $RequestURI, $aMatched)) {
                    // key:   #^(book|article)/(\d+?)/(status)/(\d+?)$#
                    // value: function($a, $b, $c, $d){}
                    $mRS = call_user_func_array($mV, $aMatched);
                } elseif (is_array($mV) && preg_match($sK, $RequestURI, $aMatched)) {
                    // key:   #^(book|article)/(\d+?)/(status)/(\d+?)$#
                    // value: array($1, $3, array('id' => $2, 'status' => $4))
                    // value: array($1_$3, null, array('id' => $2, 'status' => $4))
                    $mRS = $this->replaceRecursive($mV, $aMatched);
                }
            } elseif (is_int($sK)) {
                if (is_string($mV) && $mV[0]=='@') {
                    // value: @routeSlimeStyle
                    $mRS = call_user_func(array($this, substr($mV, 1)));
                } elseif (is_callable($mV)) {
                    // value: function(){}
                    $mRS = call_user_func($mV);
                }
            }
            if ($mRS!==false) {
                $aResult = $mRS;
                break;
            }
        }

        if (isset($aResult[0]) && is_string($aResult[0])) {
            $aResult[0] = $this->sAppNS . '\\' . $aResult[0];
        }

        return $aResult;
    }

    /**
     * generate from cli input [/your_php_bin/php /your_project/index.php -c class.method|func -p json_str
     * @return array [0=>callable, 1=>params] || []
     */
    private function generateFromCli()
    {
        $aOpt    = getopt('c:p:');
        $aResult = array();
        if (!empty($aOpt['c'])) {
            $CB = strpos($aOpt['c'], '.')===false ? $aOpt['c'] : explode('.', $aOpt['c'], 2);
            $aParam      = empty($aOpt['p']) ? array() : json_decode($aOpt['p'], true);
            $aResult     = array(
                $CB,
                $aParam,
            );
        }
        return $aResult;
    }

    private function replaceRecursive($aArr, $aMatched)
    {
        foreach ($aArr as $mK => $mRow) {
            $aArr[$mK] = is_array($mRow) ?
                $this->replaceRecursive($mRow, $aMatched):
                (
                    is_string($mRow) ?
                        str_replace($mRow, $aMatched, $mRow):
                        $mRow
                );
        }
        return $aArr;
    }

    public function routeSlimeStyle()
    {
        $aUrl   = parse_url($_SERVER['REQUEST_URI']);
        $aBlock = explode('/', strtolower(substr($aUrl['path'], 1)));

        $iLastIndex = count($aBlock) - 1;
        if ($aBlock[$iLastIndex] === '') {
            $aBlock[$iLastIndex] = $this->aConfig['action_default'];
        }

        if (count($aBlock) === 1) {
            array_unshift($aBlock, $this->aConfig['controller_default']);
        }

        $sAction = 'action' . ucfirst(array_pop($aBlock));
        foreach ($aBlock as $iK => $sBlock) {
            $aBlock[$iK] = implode('', array_map('ucfirst', explode('_', $sBlock)));
        }

        if (strpos($sAction, '.')) {
            $sAction = strstr($sAction, '.', true);
        }
        return array(
            array(
                implode('_', $aBlock),
                $sAction
            ),
            $_REQUEST,
        );
    }
}
