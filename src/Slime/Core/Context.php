<?php
namespace Slime\Core;

use Slime\Component\DataStructure\Stack;

/**
 * Class Context
 * 运行时上下文类
 * 1. 调用 makeInst 静态方法
 *     1. 生成一个唯一ID $GLOBALS['__sf_guid__']
 *     2. 生成上下文对象, 置于 $GLOBALS['__sf_context__'][$GLOBALS['__sf_guid__']], 生命周期为Global
 * 2. 通过 getInst 静态方法获取当前请求中的上下文对象
 *     1. 调用 getInst 默认获取的是前一次生成的对象
 *     2. 通过改变 $GLOBALS['__sf_guid__'] 的值可以获取到到不同的上下文对象(一般普通逻辑中无需用到)
 * 3. 通过 register 可以注册新的运行时对象
 * 4. 注册对象A, 可以以 Context::getInst()->A 取得
 *
 * @package Slime\Core
 * @property-read string                                     $sENV         当前环境(例如 publish:生产环境; development:开发环境)
 * @property-read string                                     $sRunMode     PHP运行方式, 当前支持 (cli||http)
 * @property-read string                                     $sNS          当前应用的命名空间
 * @property-read \DateTime                                  $DateTime     框架初始化时的时间对象
 * @property-read Bootstrap                                  $Bootstrap    框架核心基础对象
 * @property-read \Slime\Component\Config\Configure $Config       配置对象
 * @property-read \Slime\Component\Log\Logger       $Log          日志对象
 * @property-read \Slime\Component\Route\Router     $Route        路由对象
 * @property-read \Slime\Component\Route\CallBack   $CallBack     路由结果回调对象
 * @property-read \Slime\Component\HTTP\HttpRequest     $HttpRequest  本次Http请求生成的HttpRequest对象
 * @property-read \Slime\Component\HTTP\HttpResponse    $HttpResponse 响应本次Http请求的HttpResponse对象
 */
class Context
{
    protected $aObject = array();

    /**
     * 获取当前请求的上下文对象
     * @return \Slime\Core\Context
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
     * @param string $sVarName    对象标志(唯一, 作为调用时的Key)
     * @param mixed  $Object      对象
     * @param bool   $bOverWrite  是否自动覆盖已存在的同标志对象
     * @param bool   $bAllowExist 是否允许存在同标志对象(若此值为假, 并且存在相同标志对象, 则将抛错, 程序退出)
     */
    public function register($sVarName, $Object, $bOverWrite = true, $bAllowExist = true)
    {
        if (isset($this->aObject[$sVarName])) {
            if ($bOverWrite) {
                $this->aObject[$sVarName] = $Object;
            } else {
                if (!$bAllowExist) {
                    $this->Log->error(
                        'Object register failed. {key} has exist{object}',
                        array('key' => $sVarName, 'object' => $Object)
                    );
                    exit(1);
                }
            }
        } else {
            $this->aObject[$sVarName] = $Object;
        }
    }

    public function isRegister($sVarName)
    {
        return array_key_exists($sVarName, $this->aObject);
    }

    public function __get($sVarName)
    {
        if (!isset($this->aObject[$sVarName])) {
            $this->Log->error(
                'Object fetch failed. {key} has not exist',
                array('key' => $sVarName)
            );
            exit(1);
        }
        return $this->aObject[$sVarName];
    }

    public function copy()
    {
        $OldContext = Context::getInst();
        Context::makeInst();
        $Context = Context::getInst();
        foreach ($OldContext->aObject as $sK => $mV) {
            if (is_object($mV)) {
                $Ref = new \ReflectionObject($mV);
                try {
                    $Method = $Ref->getMethod('__clone');
                    if ($Method->isPublic()) {
                        $mV = clone $mV;
                    }
                } catch (\ReflectionException $E) {
                    $mV = clone $mV;
                }
            }
            $Context->register($sK, $mV);
        }
        return $Context;
    }

    public function destroy()
    {
        /** @var Stack\Stack $__SF_CONTEXT__ */
        global $__SF_CONTEXT__;
        $SELF = $__SF_CONTEXT__->pop();
        foreach (get_object_vars($SELF) as $sK => $mV) {
            $this->$sK = null;
        }
    }
}