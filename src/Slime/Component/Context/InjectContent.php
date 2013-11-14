<?php
namespace Slime\Component\Context;

class InjectContent
{
    protected $Context;

    public function __construct($mObjOrClassName, Context $Context)
    {
        if (is_object($mObjOrClassName)) {
            $this->Obj = $mObjOrClassName;
            $this->sClassName = get_class($mObjOrClassName);
        } else {
            $this->Obj = null;
            $this->sClassName = $mObjOrClassName;
        }
        $this->Context = $Context;
    }

    public function __get($sVar)
    {
        return $this->Obj->$sVar;
    }

    public function __call($sMethod, $aArg)
    {
        if (empty($this->Context->aInject[$this->sClassName][$sMethod])) {
            return empty($aArg) ? $this->Obj->$sMethod() : call_user_func_array(array($this->Obj, $sMethod), $aArg);
        } else {
            $aInject = $this->Context->aInject[$this->sClassName][$sMethod];
            if (!empty($aInject['replace'])) {
                return call_user_func($aInject['replace'], $this->Obj, $sMethod, $aArg);
            } else {
                $Arg = new DataContent($aArg);
                $Method = new DataContent($sMethod);
                if (isset($aInject['before'])) {
                    foreach ($aInject['before'] as $mCallBack) {
                        if (call_user_func($mCallBack, $this->Obj, $Method, $Arg)===false) {
                            break;
                        }
                    }
                }

                $mRS = empty($aArg) ? $this->Obj->$sMethod() : call_user_func_array(array($this->Obj, $sMethod), $aArg);

                if (isset($aInject['after'])) {
                    $RS = new DataContent($mRS);
                    foreach ($aInject['after'] as $mCallBack) {
                        if (call_user_func($mCallBack, $this->Obj, $Method, $Arg, $RS)===false) {
                            break;
                        }
                    }
                    $mRS = $RS->mData;
                }
                return $mRS;
            }
        }
    }
}