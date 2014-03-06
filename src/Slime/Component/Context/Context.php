<?php
namespace Slime\Component\Context;

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
    /**
     * 获取当前请求的上下文对象
     *
     * @return self 有可能为NULL
     */
    public static function getInst()
    {
        return end($GLOBALS['__SF_CONTEXT__']);
    }

    /**
     * 生成上下文
     *
     * @return self
     */
    public static function makeInst()
    {
        if (!isset($GLOBALS['__SF_CONTEXT__'])) {
            $GLOBALS['__SF_CONTEXT__'] = array();
        }
        $Obj                         = new static();
        $GLOBALS['__SF_CONTEXT__'][] = $Obj;
        return $Obj;
    }

    /**
     * 销毁栈顶元素
     */
    public static function destroy()
    {
        array_pop($GLOBALS['__SF_CONTEXT__']);
    }

    public function __get($sVar)
    {
        throw new \Exception("Please register $sVar first!");
    }

    /**
     * @param string $sVarName
     *
     * @return bool
     */
    public function isRegister($sVarName)
    {
        return property_exists($this, $sVarName);
    }

    /**
     * @param string $sVarName    标志(唯一, 作为调用时的Key)
     * @param mixed  $mEveryThing 值
     *
     * @throws \Exception
     */
    public function register($sVarName, $mEveryThing)
    {
        $this->$sVarName = $mEveryThing;
    }

    public function registerMulti(array $aKVMap)
    {
        foreach ($aKVMap as $sK => $mV) {
            $this->$sK = $mV;
        }
    }
}
