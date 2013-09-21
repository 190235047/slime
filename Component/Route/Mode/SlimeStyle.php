<?php
namespace SlimeFramework\Component\Route;

use SlimeFramework\Component\Http;

class Mode_SlimeStyle implements IMode
{
    /**
     * @param \SlimeFramework\Component\Http\IRequest $Request
     * @param \SlimeFramework\Component\Route\CallBack $CallBack
     * @return bool [true:continue next rule, false||other:break{default action}]
     */
    public function run(Http\IRequest $Request, CallBack $CallBack)
    {
        $aUrl   = parse_url($Request->getRequestURI());
        $aBlock = explode('/', strtolower(substr($aUrl['path'], 1)));

        $iLastIndex = count($aBlock) - 1;
        if ($aBlock[$iLastIndex] === '') {
            $aBlock[$iLastIndex] = 'actionDefault';
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

        $CallBack->setCBObject(implode('_', $aBlock), $sAction);

        return false;
    }
}