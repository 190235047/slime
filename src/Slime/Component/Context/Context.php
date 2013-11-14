<?php
namespace Slime\Component\Context;

use Slime\Component\DataStructure\Stack;

class Context
{
    public $aInject = array();
    protected $aStorage = array();

    /**
     * 获取当前请求的上下文对象
     * @return \Slime\Component\Context\Context|null
     */
    public static function getInst()
    {
        /** @var Stack\Stack $__SF_CONTEXT__ */
        global $__SF_CONTEXT__;
        return empty($__SF_CONTEXT__) ? null : $__SF_CONTEXT__->current();
    }

    /**
     * 生成上下文
     */
    public static function makeInst()
    {
        if (!isset($GLOBALS['__SF_CONTEXT__'])) {
            $GLOBALS['__SF_CONTEXT__'] = new Stack\Stack();
        }

        /** @var Stack\Stack $__SF_CONTEXT__ */
        global $__SF_CONTEXT__;
        $__SF_CONTEXT__->push(new self());
    }


    public function isRegister($sVarName)
    {
        return array_key_exists($sVarName, $this->aStorage);
    }

    public function __get($sVarName)
    {
        if (!isset($this->aStorage[$sVarName])) {
            throw new \Exception(
                "Object register failed. {$sVarName} has not exist"
            );
        }
        return $this->aStorage[$sVarName];
    }

    /**
     * @param string $sVarName    标志(唯一, 作为调用时的Key)
     * @param mixed  $mEveryThing 值
     * @param bool   $bOverWrite  是否自动覆盖已存在的同标志对象
     * @param bool   $bAllowExist 是否允许存在同标志对象(若此值为假, 并且存在相同标志对象, 则将抛错, 程序退出)
     *
     * @throws \Exception
     */
    public function register($sVarName, $mEveryThing, $bOverWrite = true, $bAllowExist = true)
    {
        if (isset($this->aStorage[$sVarName])) {
            if ($bOverWrite) {
                $this->aStorage[$sVarName] = $mEveryThing;
            } else {
                if (!$bAllowExist) {
                    throw new \Exception(
                        "Object register failed. {$sVarName} has exist"
                    );
                }
            }
        } else {
            $this->aStorage[$sVarName] = $mEveryThing;
        }
    }

    public function registerObjWithArgs($sVarName, $sClassName, array $aArgs = array(), $bOverWrite = true, $bAllowExist = true)
    {
        $this->register($sVarName, self::createObj($sClassName, $aArgs), $bOverWrite, $bAllowExist);
    }

    public function registerObjWithArgsAndContent($sVarName, $sClassName, array $aArgs = array(), $bOverWrite = true, $bAllowExist = true)
    {
        $this->register($sVarName, new InjectContent(self::createObj($sClassName, $aArgs), $this), $bOverWrite, $bAllowExist);
    }

    public static function createObj($sClassName, array $aArgs = array())
    {
        $sClassName = ltrim($sClassName, '/');
        if (empty($aArgs)) {
            return new $sClassName();
        } else {
            $Ref = new \ReflectionClass($sClassName);
            return $Ref->newInstanceArgs($aArgs);
        }
    }
}