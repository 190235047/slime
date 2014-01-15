<?php
namespace Slime\Component\Context;

use Slime\Component\Helper\Sugar;

/**
 * Class Context
 *
 * 运行时上下文
 * 1. 调用 makeInst 静态方法, 生成 Context 对象, 压入(后入先出的数据结构: Slime\Component\DataStructure\Stack)
 * 2. 通过 getInst 静态方法获取当前请求中的上下文对象, 即栈顶 Context
 * 3. 通过 register 可以注册新的运行时对象
 * 4. 注册对象A, 可以 Context::getInst()->A 取得
 * 5. 销毁对象需显式调用 destroy 方法, 即弹出栈顶 Context 对象, 并销毁此对象中注册的所有元素
 *
 * @package Slime\Component\Context
 * @author  smallslime@gmail.com
 */
class Context
{
    protected $aStorage = array();

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
     * @param string $sVarName
     * @param bool   $bForce
     *
     * @return mixed
     * @throws \Exception
     */
    public function get($sVarName, $bForce = true)
    {
        if (!isset($this->aStorage[$sVarName])) {
            if ($bForce) {
                throw new \Exception(
                    "Object register failed. {$sVarName} has not exist"
                );
            } else {
                return null;
            }
        }
        return $this->aStorage[$sVarName];
    }

    /**
     * 获取当前请求的上下文对象
     *
     * @return $this 有可能为NULL
     */
    public static function getInst()
    {
        return end($GLOBALS['__SF_CONTEXT__']);
    }

    /**
     * 生成上下文
     */
    public static function makeInst()
    {
        if (!isset($GLOBALS['__SF_CONTEXT__'])) {
            $GLOBALS['__SF_CONTEXT__'] = array();
        }
        $GLOBALS['__SF_CONTEXT__'][] = new static();
    }

    /**
     * 销毁栈顶元素
     */
    public static function destroy()
    {
        array_pop($GLOBALS['__SF_CONTEXT__']);
    }

    /**
     * @param string $sVarName
     *
     * @return bool
     */
    public function isRegister($sVarName)
    {
        return array_key_exists($sVarName, $this->aStorage);
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
                    throw new \Exception("Object register failed. {$sVarName} has exist");
                }
            }
        } else {
            $this->aStorage[$sVarName] = $mEveryThing;
        }
    }

    public function registerMulti(array $aKVMap, $bOverWrite = true, $bAllowExist = true)
    {
        if (!$bAllowExist) {
            if (count($aArr = array_intersect_key($this->aStorage, $aKVMap)) === 0) {
                throw new \Exception(sprintf("Object register failed. [%s] has exist"), json_encode($aArr));
            }
        }
        $this->aStorage = $bOverWrite ? array_merge($this->aStorage, $aKVMap) : array_merge($aKVMap, $this->aStorage);
    }

    /**
     * @param string $sVarName
     * @param string $sClassName
     * @param array  $aArgs
     * @param bool   $bOverWrite
     * @param bool   $bAllowExist
     */
    public function registerObjWithArgs(
        $sVarName,
        $sClassName,
        array $aArgs = array(),
        $bOverWrite = true,
        $bAllowExist = true
    ) {
        $this->register($sVarName, Sugar::createObj($sClassName, $aArgs), $bOverWrite, $bAllowExist);
    }
}