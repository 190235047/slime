<?php
namespace Slime\Component\Route;

/**
 * Class CallBack
 *
 * @package Slime\Component\Route
 * @author  smallslime@gmail.com
 */
class CallBack
{
    public $mCallable;
    public $aParam;
    public $sNSPre;
    public $aObjInitParam = null;
    public $bAsFunc = false;

    /**
     * @param string $sNSPre
     */
    public function __construct($sNSPre)
    {
        $this->sNSPre = $sNSPre;
    }

    /**
     * @param mixed  $mClassNameOrObject
     * @param string $sMethod
     * @param array  $aObjInitParam
     *
     * @return $this
     */
    public function setCBObject($mClassNameOrObject, $sMethod, array $aObjInitParam = array())
    {
        if (is_string($mClassNameOrObject)) {
            $this->aObjInitParam = $aObjInitParam;
            $this->mCallable     = array($this->sNSPre . '\\' . $mClassNameOrObject, $sMethod);
        } else {
            $this->mCallable = array($mClassNameOrObject, $sMethod);
        }
        return $this;
    }

    /**
     * @param string $sClassName
     * @param string $sMethod
     *
     * @return $this
     */
    public function setCBClass($sClassName, $sMethod)
    {
        $this->mCallable = array($this->sNSPre . '\\' . $sClassName, $sMethod);
        return $this;
    }

    /**
     * @param mixed $mFuncNameOrClosure
     *
     * @return $this
     */
    public function setCBFunc($mFuncNameOrClosure)
    {
        $this->bAsFunc = true;
        if ($mFuncNameOrClosure instanceof \Closure) {
            $this->mCallable = $mFuncNameOrClosure;
        } else {
            $this->mCallable = $this->sNSPre . '\\' . $mFuncNameOrClosure;
        }
        return $this;
    }

    /**
     * @param array $aParam
     *
     * @return $this
     */
    public function setParam(array $aParam = array())
    {
        $this->aParam = $aParam;
        return $this;
    }

    /**
     * @throws \RuntimeException
     */
    public function call()
    {
        if ($this->bAsFunc === true) {
            # call business logic
            call_user_func($this->mCallable, $this->aParam);
        } else {
            $mClassOrObj = $this->mCallable[0];

            # reflection need cache @todo
            if (is_array($this->aObjInitParam)) {
                $Ref                = new \ReflectionClass($mClassOrObj);
                $this->mCallable[0] = $mClassOrObj = $Ref->newInstanceArgs($this->aObjInitParam); //create object
            } elseif (is_object($mClassOrObj)) {
                $Ref = new \ReflectionObject($mClassOrObj);
            } else { //array
                $Ref = new \ReflectionClass($mClassOrObj);
            }

            # get all public method map
            $aMethod = array();
            foreach ($Ref->getMethods(\ReflectionMethod::IS_PUBLIC) as $Method) {
                $aMethod[$Method->getName()] = true;
            }

            # find method
            $sMid = $this->mCallable[1];
            if (!isset($aMethod[$sMid])) {
                throw new RouteFailException(
                    sprintf(
                        '[ROUTE] : There is no method[%s] in class[%s]',
                        $sMid,
                        is_object($mClassOrObj) ? get_class($mClassOrObj) : $mClassOrObj
                    )
                );
            }

            # before and after
            $sBefore       = $sAfter = null;
            $sExpectBefore = "__before_{$sMid}__";
            $sExpectAfter  = "__after_{$sMid}__";
            if (isset($aMethod[$sExpectBefore])) {
                $sBefore = $sExpectBefore;
            } elseif (isset($aMethod['__before__'])) {
                $sBefore = '__before__';
            }
            if (isset($aMethod[$sExpectAfter])) {
                $sAfter = $sExpectAfter;
            } elseif (isset($aMethod['__after__'])) {
                $sAfter = '__after__';
            }

            # call
            $bContinue = true;
            if ($sBefore !== null) {
                $bContinue = call_user_func(array($mClassOrObj, $sBefore));
            }
            if ($bContinue !== false) {
                $bContinue = call_user_func($this->mCallable, $this->aParam);
            }
            if ($bContinue !== false && $sAfter !== null) {
                call_user_func(array($mClassOrObj, $sAfter));
            }
        }
    }
}