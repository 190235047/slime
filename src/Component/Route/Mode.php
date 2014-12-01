<?php
namespace Slime\Component\Route;

use Slime\Component\Support\Str;
use Slime\Component\Support\Url;

/**
 * Class Mode
 *
 * @package Slime\Component\Route
 * @author  smallslime@gmail.com
 */
class Mode
{
    /**
     * @param \Slime\Component\Http\REQ    $REQ
     * @param \Slime\Component\Http\RESP   $RESP
     * @param \Slime\Component\Support\Context $CTX
     * @param array                            $aSetting
     *
     * @return bool
     * @throws RouteException
     */
    public static function slimeHttp_Page($REQ, $RESP, $CTX, $aSetting)
    {
        $aBlock = Url::parse($REQ->getUrl(), true, false);
        $aPath  = $aBlock['path'];
        if ($aPath[($i = count($aPath) - 1)] === '') {
            $aPath[$i] = $aSetting['default_action'];
        }
        if (count($aPath) === 1) {
            array_unshift($aPath, $aSetting['default_controller']);
        }
        $sAction     = array_pop($aPath);
        $sController = $aSetting['controller_pre'] .
            implode('_', array_map(array('\\Slime\\Component\\Support\\Str', 'camel'), $aPath));
        if (($iPos = strrpos($sAction, '.')) === false) {
            $sExt = isset($aSetting['default_ext']) ? $aSetting['default_ext'] : null;
        } else {
            $sExt    = substr($sAction, $iPos + 1);
            $sAction = substr($sAction, 0, $iPos);
        }
        $sAction = $aSetting['action_pre'] . Str::camel($sAction);
        if (($sReqMethod = $REQ->getMethod()) !== 'GET') {
            $sAction .= '__' . $sReqMethod;
        }

        self::objCall(
            $CTX,
            $sController,
            $sAction,
            array(
                '__EXT__'        => $sExt,
                '__CONTROLLER__' => $sController,
                '__ACTION__'     => $sAction,
                '__SETTING__'    => $aSetting
            )
        );

        return false;
    }

    /**
     * @param \Slime\Component\Http\REQ    $REQ
     * @param \Slime\Component\Http\RESP   $RESP
     * @param \Slime\Component\Support\Context $CTX
     * @param array                            $aSetting
     *
     * @return bool
     * @throws RouteException
     */
    public static function slimeHttp_REST($REQ, $RESP, $CTX, $aSetting)
    {
        $aBlock = Url::parse($REQ->getUrl(), true, false);
        $aPath  = $aBlock['path'];
        $iCount = count($aPath);
        $sLast  = &$aPath[$iCount - 1];
        if ($sLast == '') {
            $sLast = $aSetting['default_controller'];
        }
        if (($iC = count($aPath)) < 2 || $iC % 2 !== 0) {
            throw new RouteException('[ROUTE] ; Url path block count I must accord with: I >= 2 && I % 2 == 0', 400);
        }
        if (($iPos = strrpos($sLast, '.')) === false) {
            $sExt = $aSetting['default_ext'];
        } else {
            $sExt  = substr($sLast, $iPos + 1);
            $sLast = substr($sLast, 0, $iPos);
        }
        $aParam = array('__EXT__' => $sExt, '__SETTING__' => $aSetting);
        $sVer   = strtoupper(array_shift($aPath));
        for ($i = 0, $iC = count($aPath); $i < $iC; $i += 2) {
            $aParam[$aPath[$i]] = $aPath[$i + 1];
        }
        $sAction     = strtolower($REQ->getMethod());
        $sController = $sVer . '_' . Str::camel($sLast);

        self::objCall($CTX, $sController, $sAction, $aParam);

        return false;
    }

    /**
     * @param array                            $aArgv
     * @param \Slime\Component\Support\Context $CTX
     * @param array                            $aSetting
     *
     * @return bool
     * @throws RouteException
     */
    public static function slimeHttp_Cli($aArgv, $CTX, $aSetting)
    {
        if (strpos($aArgv[1], '.') === false) {
            $aBlock = array($aArgv[1], $aSetting['default_controller']);
        } else {
            $aBlock = explode('.', $aArgv[1], 2);
        }
        $aParam                = empty($aArgv[2]) ? array() : json_decode($aArgv[2], true);
        $aParam['__SETTING__'] = $aSetting;

        self::objCall(
            $CTX,
            $aSetting['default_controller'] . $aBlock[0],
            $aSetting['default_action'] . $aBlock[1],
            $aParam
        );

        return false;
    }

    /**
     * @param \Slime\Component\Support\Context $CTX
     * @param string                           $sController
     * @param string                           $sAction
     * @param array                            $aParam
     *
     * @return bool
     * @throws RouteException
     */
    public static function objCall($CTX, $sController, $sAction, $aParam = array())
    {
        # for throw exception
        $mAL = function ($sClass) {
            throw new RouteException("[ROUTE] ; Controller[$sClass] is not found", 404);
        };
        spl_autoload_register($mAL);
        $Obj = new $sController($CTX, $aParam);
        spl_autoload_unregister($mAL);


        if (isset($aParam['__SETTING__']['__AOP__']) && $aParam['__SETTING__']['__AOP__'] === false) {
            $Obj->$sAction();
        } else {
            $Ref = new \ReflectionClass($sController);
            # get all public method map
            $aMethod = array();
            foreach ($Ref->getMethods(\ReflectionMethod::IS_PUBLIC) as $Method) {
                $aMethod[$Method->getName()] = true;
            }

            # find method
            if (!isset($aMethod[$sAction])) {
                throw new RouteException("[ROUTE] ; Action[$sAction] is not found in Controller[$sController]", 404);
            }

            # before and after
            $sBefore       = $sAfter = null;
            $sExpectBefore = "__before_{$sAction}__";
            if (isset($aMethod[$sExpectBefore])) {
                $sBefore = $sExpectBefore;
            } elseif (isset($aMethod['__before__'])) {
                $sBefore = '__before__';
            }
            $sExpectAfter = "__after_{$sAction}__";
            if (isset($aMethod[$sExpectAfter])) {
                $sAfter = $sExpectAfter;
            } elseif (isset($aMethod['__after__'])) {
                $sAfter = '__after__';
            }

            # call
            $bContinue = true;
            if ($sBefore !== null) {
                $bContinue = call_user_func(array($Obj, $sBefore));
            }
            if ($bContinue !== false) {
                $bContinue = call_user_func(array($Obj, $sAction));
            }
            if ($bContinue !== false && $sAfter !== null) {
                call_user_func(array($Obj, $sAfter));
            }
        }

        return true;
    }
}