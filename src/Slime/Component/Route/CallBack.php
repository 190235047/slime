<?php
namespace Slime\Component\Route;

class CallBack
{
    public $mCallable;
    public $aParam;
    public $sNSPre;
    public $aObjInitParam = null;
    public $bAsFunc = false;

    public function __construct($sNSPre)
    {
        $this->sNSPre = $sNSPre;
    }

    public function setCBObject($mClassNameOrObject, $sMethod, $aObjInitParam = null)
    {
        if (is_string($mClassNameOrObject)) {
            $this->aObjInitParam = $aObjInitParam === null ?
                array() :
                (is_array($aObjInitParam) ? $aObjInitParam : array($aObjInitParam));
            $this->mCallable = array($this->sNSPre . '\\' . $mClassNameOrObject, $sMethod);
        } else {
            $this->mCallable = array($mClassNameOrObject, $sMethod);
        }
    }

    public function setCBClass($sClassName, $sMethod)
    {
        $this->mCallable = array($this->sNSPre . '\\' . $sClassName, $sMethod);
    }

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

    public function setParam($aParam = array())
    {
        $this->aParam = $aParam;
        return $this;
    }

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
                throw new \RuntimeException(
                    sprintf('There is no method[%s] in class[%s]', $sMid, $mClassOrObj)
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