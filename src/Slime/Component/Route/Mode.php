<?php
namespace Slime\Component\Route;

use Slime\Component\Http;

/**
 * Class Mode
 *
 * @package Slime\Component\Route
 * @author  smallslime@gmail.com
 */
class Mode
{
    /**
     * @param Http\HttpRequest  $Request
     * @param Http\HttpResponse $Response
     * @param bool              $bContinue
     * @param string            $sAppNs
     *
     * @return CallBack
     */
    public static function slimeHttp(Http\HttpRequest $Request, Http\HttpResponse $Response, &$bContinue, $sAppNs)
    {
        $aUrl      = parse_url($Request->getRequestURI());
        $aUrlBlock = explode('/', strtolower(substr($aUrl['path'], 1)));

        $iLastIndex = count($aUrlBlock) - 1;
        if ($aUrlBlock[$iLastIndex] === '') {
            $aUrlBlock[$iLastIndex] = 'default';
        }

        if (count($aUrlBlock) === 1) {
            array_unshift($aUrlBlock, 'Main');
        }

        $sAction = ucfirst(array_pop($aUrlBlock));
        foreach ($aUrlBlock as $iK => $sBlock) {
            $aUrlBlock[$iK] = implode('', array_map('ucfirst', explode('_', $sBlock)));
        }

        if (strpos($sAction, '.')) {
            $sAction = strstr($sAction, '.', true);
        }
        $sAction = 'action' . implode('', array_map('ucfirst', explode('_', $sAction)));

        $sRequestMethod = $Request->getRequestMethod();
        if ($sRequestMethod !== 'GET') {
            $sAction .= '_' . $sRequestMethod;
        }

        $CallBack = new CallBack($sAppNs);
        $CallBack->setCBObject('ControllerHttp_' . implode('_', $aUrlBlock), $sAction);

        return $CallBack;
    }

    /**
     * @param array  $aArg
     * @param bool   $bContinue
     * @param string $sAppNs
     *
     * @return CallBack
     */
    public static function slimeCli($aArg, &$bContinue, $sAppNs)
    {
        if (strpos($aArg[1], '.') === false) {
            $aBlock = array($aArg[1], 'Default');
        } else {
            $aBlock = explode('.', $aArg[1], 2);
        }
        $aParam = empty($aArg[2]) ?
            array() :
            json_decode($aArg[2], true);

        $CallBack = new CallBack($sAppNs);
        $CallBack->setCBObject("ControllerCli_{$aBlock[0]}", "action{$aBlock[1]}", array($aParam));

        return $CallBack;
    }
}