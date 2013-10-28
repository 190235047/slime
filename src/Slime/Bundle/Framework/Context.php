<?php
namespace Slime\Bundle\Framework;

use Slime\Component\DataStructure\Stack;

/**
 * Class Context
 * 运行时上下文类
 * 1. 调用 makeInst 静态方法, 生成 Context 对象, 压栈(后入先出的数据结构: Slime\Component\DataStructure\Stack)
 * 2. 通过 getInst 静态方法获取当前请求中的上下文对象, 即栈顶 Context
 * 3. 通过 register 可以注册新的运行时对象
 * 4. 注册对象A, 可以 Context::getInst()->A 取得
 * 5. 销毁对象需显式调用 destroy 方法, 即弹出栈顶 Context 对象, 并销毁此对象中 register 的所有对象. 若栈顶并非 $this , 不做弹出
 *
 * @package Slime\Bundle\Framework
 * @author  smallslime@gmail.com
 *
 * @property-read string                                     $sENV         当前环境(例如 publish:生产环境; development:开发环境)
 * @property-read string                                     $sRunMode     PHP运行方式, 当前支持 (cli||http)
 * @property-read string                                     $sNS          当前应用的命名空间
 * @property-read \DateTime                                  $DateTime     框架初始化时的时间对象
 * @property-read Bootstrap                                  $Bootstrap    框架核心基础对象
 * @property-read \Slime\Component\Config\Configure          $Config       配置对象
 * @property-read \Slime\Component\Log\Logger                $Log          日志对象
 * @property-read \Slime\Component\Route\Router              $Route        路由对象
 * @property-read \Slime\Component\Route\CallBack            $CallBack     路由结果回调对象
 * @property-read \Slime\Component\HTTP\HttpRequest          $HttpRequest  本次Http请求生成的HttpRequest对象
 * @property-read \Slime\Component\HTTP\HttpResponse         $HttpResponse 响应本次Http请求的HttpResponse对象
 */
class Context
{
    protected $aStorage = array();

    /**
     * 获取当前请求的上下文对象
     * @return \Slime\Bundle\Framework\Context|bool
     */
    public static function getInst()
    {
        /** @var Stack\Stack $__SF_CONTEXT__ */
        global $__SF_CONTEXT__;
        return $__SF_CONTEXT__->current();
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

    private function __construct()
    {
    }

    private function __clone()
    {
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

    public function destroy()
    {
        /** @var Stack\Stack $__SF_CONTEXT__ */
        global $__SF_CONTEXT__;
        $SELF = $__SF_CONTEXT__->current();
        if ($SELF === $this) {
            $__SF_CONTEXT__->pop();
            foreach (get_object_vars($SELF) as $sK => $mV) {
                unset($this->$sK);
            }
        }
    }
}