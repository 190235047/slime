<?php
namespace SlimeFramework\Component\Route;

use SlimeFramework\Component\Http;

class Mode_SlimeStyle implements IMode
{
    /**
     * @param \SlimeFramework\Component\Http\Request $Request
     * @param \SlimeFramework\Component\Route\CallBack $CallBack
     * @return bool [true:continue next rule, false||other:break{default action}]
     */
    public function runHttp(Http\Request $Request, CallBack $CallBack)
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

        $sAction = 'action' . ucfirst(array_pop($aBlock));
        foreach ($aBlock as $iK => $sBlock) {
            $aBlock[$iK] = implode('', array_map('ucfirst', explode('_', $sBlock)));
        }

        if (strpos($sAction, '.')) {
            $sAction = strstr($sAction, '.', true);
        }

        $CallBack->setCBObject('ControllerHttp_' . implode('_', $aBlock), $sAction);

        return false;
    }

    public function runCli($aArg, CallBack $CallBack)
    {
        if (strpos($aArg[0], '.') === false) {
            $aArr = explode('.', $aArg[0], 2);
        } else {
            $aArr[0] = $aArg[0];
            $aArr[1] = 'Default';
        }
        $CallBack->setCBObject("ControllerCli_{$aArr[0]}", "action{$aArr[1]}");

        $aParam  = empty($aArg[1]) ? array() : json_decode($aArg[1], true);
        $CallBack->setParam($aParam);
        return $CallBack;
    }
}