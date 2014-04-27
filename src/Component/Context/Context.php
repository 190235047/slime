<?php
namespace Slime\Component\Context;

use Slime\Component\Config\Configure;
use Slime\Component\Helper\Packer;

/**
 * Class Context
 *
 * @package Slime\Component\Context
 * @author  smallslime@gmail.com
 */
class Context
{
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
     *
     * @return $this
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


    /** @var array */
    protected $__aVarKey__;
    /** @var array */
    protected $__aModuleConf__;

    /**
     * @param string $sVarName
     *
     * @return bool
     */
    public function isRegistered($sVarName)
    {
        return isset($this->__aVarKey__[$sVarName]);
    }

    /**
     * @param string $sVarName
     *
     * @return mixed
     */
    public function get($sVarName)
    {
        return isset($this->__aVarKey__[$sVarName]) ? $this->$sVarName : null;
    }

    /**
     * @param string $sVarName    标志(唯一, 作为调用时的Key)
     * @param mixed  $mEveryThing 值
     */
    public function register($sVarName, $mEveryThing)
    {
        $this->__aVarKey__[$sVarName] = true;
        $this->$sVarName              = $mEveryThing;
    }

    /**
     * @param array $aKVMap [k:v, ...]
     */
    public function registerMulti(array $aKVMap)
    {
        foreach ($aKVMap as $sK => $mV) {
            $this->__aVarKey__[$sK] = true;
            $this->$sK              = $mV;
        }
    }

    public function __get($sVarName)
    {
        if (!$this->isRegistered('Config')) {
            throw new \DomainException("[CTX] : Auto register[$sVarName] failed. Please register Config before");
        }
        /** @var \Slime\Component\Config\IAdaptor $CFG */
        $CFG = $this->Config;

        # get all conf and tidy only once
        if ($this->__aModuleConf__ === null) {
            $aAllCFG = $CFG->setTmpParseMode(false)->get("module");
            $CFG->resetParseMode();
            $sRunMode = $this->sRunMode;
            foreach ($aAllCFG as $aItem) {
                if (!empty($aItem['run_mode']) && $aItem['run_mode'] !== $sRunMode) {
                    continue;
                }
                $this->__aModuleConf__[$aItem['module']] = $aItem;
            }
        }

        # get conf
        if (empty($this->__aModuleConf__[$sVarName])) {
            throw new \DomainException("[CTX] : Module config[module.$sVarName] is not exists");
        }
        $aModuleConfig = $this->__aModuleConf__[$sVarName];
        $aModuleConfig = Configure::parseRecursion($aModuleConfig, $CFG);

        # create instance
        if (!isset($aModuleConfig['params'])) {
            $aModuleConfig['params'] = array();
        }
        if (!empty($aModuleConfig['factory'])) {
            $Obj = call_user_func_array(
                array($aModuleConfig['class'], $aModuleConfig['factory']),
                $aModuleConfig['params']
            );
        } else {
            if (empty($aModuleConfig['params'])) {
                $Obj = new $aModuleConfig['class']();
            } else {
                $Ref = new \ReflectionClass($aModuleConfig['class']);
                $Obj = $Ref->newInstanceArgs($aModuleConfig['params']);
            }
        }

        # if packer
        if (!empty($aModuleConfig['packer'])) {
            $Obj = new Packer($Obj, $aModuleConfig['packer']);
        }

        # register
        $this->$sVarName              = $Obj;
        $this->__aVarKey__[$sVarName] = true;
        return $Obj;
    }
}
