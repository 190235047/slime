<?php
namespace Slime\Component\Route;

use Slime\Component\Http\HttpRequest;
use Slime\Component\Http\HttpResponse;

/**
 * Class Mode
 *
 * @package Slime\Component\Route
 * @author  smallslime@gmail.com
 */
class Mode
{
    /**
     * @param HttpRequest  $REQ
     * @param HttpResponse $RES
     * @param HitMode      $HitMode
     * @param string       $sControllerPre
     * @param string       $sActionPre
     *
     * @return CallBack
     */
    public static function slimeHttp(
        $REQ,
        $RES,
        $HitMode,
        $sControllerPre,
        $sActionPre
    ) {
        $aUrl      = parse_url($REQ->getRequestURI());
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

        list($sAction, $sExt) = array_replace(array('', 'html'), explode('.', $sAction, 2));
        $sAction = implode('', array_map('ucfirst', explode('_', $sAction)));

        $sRequestMethod = $REQ->getRequestMethod();
        if ($sRequestMethod !== 'GET') {
            $sAction .= '_' . $sRequestMethod;
        }

        $CallBack = new CallBack($sControllerPre, $sActionPre);
        $CallBack->setCBObject(
            implode('_', $aUrlBlock),
            $sAction,
            array(array('__ext__' => strtolower($sExt)))
        );

        return $CallBack;
    }

    /**
     * @param HttpRequest  $REQ
     * @param HttpResponse $RES
     * @param HitMode      $HitMode
     * @param string       $sControllerPre
     * @param string       $sActionPre
     *
     * @return CallBack
     */
    public static function slimeREST(
        $REQ,
        $RES,
        $HitMode,
        $sControllerPre,
        $sActionPre
    ) {
        $aURI  = parse_url($REQ->getRequestURI());
        $aPath = explode('/', trim($aURI['path'], '/'));
        if (count($aPath) < 2) {
            return null;
        }

        $sExt    = substr(strrpos($aURI['path'], '.'), 1);
        $aParam  = array('__ext__' => $sExt);
        $Version = strtoupper(array_shift($aPath));
        $sEntity = array_pop($aPath);
        if (($i = strpos($sEntity, '.')) !== false) {
            $sExt    = substr($sEntity, $i + 1);
            $sEntity = substr($sEntity, 0, $i);
        } else {
            $sExt = 'json';
        }
        if (($iCount = count($aPath)) >= 2 && $iCount % 2 === 0) {
            for ($i = 0; $i < $iCount; $i += 2) {
                $aParam[$aPath[$i]] = $aPath[$i + 1];
            }
        }
        $aParam['__ext__'] = $sExt;
        $sMethod           = strtolower($REQ->getRequestMethod());
        $sController       = $Version . '_' . implode(
                '',
                array_map(
                    function ($sPart) {
                        return ucfirst(strtolower($sPart));
                    },
                    explode('_', $sEntity)
                )
            );
        $CallBack          = new CallBack($sControllerPre, $sActionPre);
        $CallBack->setCBObject($sController, $sMethod, array($aParam));

        return $CallBack;
    }

    /**
     * @param array   $aArg
     * @param HitMode $HitMode
     * @param string  $sControllerPre
     * @param string  $sActionPre
     *
     * @return CallBack
     */
    public static function slimeCli($aArg, $HitMode, $sControllerPre = '', $sActionPre = '')
    {
        if (strpos($aArg[1], '.') === false) {
            $aBlock = array($aArg[1], 'Default');
        } else {
            $aBlock = explode('.', $aArg[1], 2);
        }
        $aParam = empty($aArg[2]) ?
            array() :
            json_decode($aArg[2], true);

        $CallBack = new CallBack($sControllerPre, $sActionPre);
        $CallBack->setCBObject($aBlock[0], $aBlock[1], array($aParam));

        return $CallBack;
    }
}