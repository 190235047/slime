<?php
namespace Slime\Component\Route;

use Slime\Component\Http;

class Mode_SlimeStyle implements IMode
{
    /**
     * @param \Slime\Component\Http\HttpRequest   $Request
     * @param \Slime\Component\Route\CallBack     $CallBack
     *
     * @return bool [true:continue next rule, false||other:break{default action}]
     */
    public function runHttp(Http\HttpRequest $Request, CallBack $CallBack)
    {
        $aUrl   = parse_url($Request->getRequestURI());
        $aBlock = explode('/', strtolower(substr($aUrl['path'], 1)));

        $iLastIndex = count($aBlock) - 1;
        if ($aBlock[$iLastIndex] === '') {
            $aBlock[$iLastIndex] = 'default';
        }

        if (count($aBlock) === 1) {
            array_unshift($aBlock, 'Main');
        }

        $sAction = ucfirst(array_pop($aBlock));
        foreach ($aBlock as $iK => $sBlock) {
            $aBlock[$iK] = implode('', array_map('ucfirst', explode('_', $sBlock)));
        }

        if (strpos($sAction, '.')) {
            $sAction = strstr($sAction, '.', true);
        }
        $sAction = 'action' . implode('', array_map('ucfirst', explode('_', $sAction)));;

        $sRequestMethod = $Request->getRequestMethod();
        if ($sRequestMethod !== 'GET') {
            $sAction .= '_' . $sRequestMethod;
        }

        $CallBack->setCBObject('ControllerHttp_' . implode('_', $aBlock), $sAction);

        return false;
    }

    public function runCli($aArg, CallBack $CallBack)
    {
        if (strpos($aArg[1], '.') === false) {
            $aBlock = array($aArg[1], 'Default');
        } else {
            $aBlock = explode('.', $aArg[1], 2);
        }
        $aParam = empty($aArg[2]) ?
            array() :
            json_decode($aArg[2], true);

        $CallBack->setCBObject("ControllerCli_{$aBlock[0]}", "action{$aBlock[1]}", array($aParam));

        return $CallBack;
    }
}