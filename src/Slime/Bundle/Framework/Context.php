<?php
namespace Slime\Bundle\Framework;

use Slime\Component\Config\Configure;
use Slime\Component\Helper\Packer;
use Slime\Component\Context\Context as ContextCore;

/**
 * Class Context
 *
 * @package Slime\Bundle\Framework
 * @author  smallslime@gmail.com
 *
 * @property-read string                                     $sENV         当前环境(例如 publish:生产环境; development:开发环境)
 * @property-read string                                     $sRunMode     PHP运行方式, 当前支持 (cli||http)
 * @property-read string                                     $sNS          当前应用的命名空间
 * @property-read Bootstrap                                  $Bootstrap    框架核心基础对象
 * @property-read \Slime\Component\Config\IAdaptor           $Config       配置对象
 * @property-read \Slime\Component\Log\Logger                $Log          日志对象
 * @property-read \Slime\Component\Route\Router              $Route        路由对象
 * @property-read \Slime\Component\Route\CallBack            $CallBack     路由结果回调对象
 * @property-read \Slime\Component\HTTP\HttpRequest          $HttpRequest  本次Http请求生成的HttpRequest对象
 * @property-read \Slime\Component\HTTP\HttpResponse         $HttpResponse 响应本次Http请求的HttpResponse对象
 * @property-read array                                      $aArgv        本次CLI请求的参数数组
 * @property-read \Slime\Component\I18N\I18N                 $I18N         多语言对象
 * @property-read \Slime\Component\View\IAdaptor             $View         模板对象
 * @property-read mixed                                      $mCBErrPage   错误页回调
 */
class Context extends ContextCore
{
    protected $__aModuleConf__;

    public function __get($sVarName)
    {
        if ($this->isRegister($sVarName)) {
            return $this->$sVarName;
        } else {
            # get all conf and tidy only once
            if ($this->__aModuleConf__ === null) {
                if (!$this->isRegister('Config')) {
                    throw new \DomainException("Auto register[$sVarName] failed. Please register Config before");
                }
                $aAllCFG = $this->Config->setTmpParseMode(false)->get("module");
                $this->Config->resetParseMode();
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
                throw new \DomainException("Module config[module.$sVarName] is not exists");
            }
            $aModuleConfig = $this->__aModuleConf__[$sVarName];
            $aModuleConfig = Configure::parseRecursion($aModuleConfig, $this->Config);

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
            $this->$sVarName = $Obj;
            $this->__aVarKey__[$sVarName] = true;
            return $Obj;
        }
    }
}