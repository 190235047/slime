<?php
namespace Slime\Bundle\Framework;

use Slime\Component\Config\Configure;
use Slime\Component\Config\IAdaptor;
use Slime\Component\Helper\Packer;

/**
 * Class Context
 *
 * @package Slime\Bundle\Framework
 * @author  smallslime@gmail.com
 *
 * @property-read array                                      $aAppDir      应用目录数组
 * @property-read string                                     $sENV         当前环境(例如 publish:生产环境; development:开发环境)
 * @property-read string                                     $sRunMode     PHP运行方式, 当前支持 (cli||http)
 * @property-read string                                     $sNS          当前应用的命名空间
 * @property-read \DateTime                                  $DateTime     框架初始化时的时间对象
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
 */
class Context extends \Slime\Component\Context\Context
{
    protected $aAllModuleConfig;

    public function __get($sVarName)
    {
        if (!isset($this->aStorage[$sVarName])) {
            # get conf
            $C = $this->Config;
            $aModuleConfig = $C->setTmpParseMode(false)->get("module.$sVarName");
            if (empty($aModuleConfig)) {
                throw new \Exception("Module config[module.$sVarName] is not exists");
            }

            $aModuleConfig = Configure::parseRecursion($aModuleConfig, $C);
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
            $this->register($sVarName, $Obj, false);
        }
        return $this->aStorage[$sVarName];
    }

    /**
     * @param string   $sModuleName
     * @param IAdaptor $Config
     *
     * @return object
     * @throws \Exception
     */
    public function registerModule($sModuleName, IAdaptor $Config = null)
    {
        if ($Config === null) {
            $Config = $this->Config;
        }

        $aData = $Config->get("module.$sModuleName");
        if (empty($aData['class'])) {
            throw new \Exception("Module[$sModuleName] config error");
        }

        if (empty($aData['params'])) {
            return new $aData['class']();
        } else {
            $Ref = new \ReflectionClass($aData['class']);
            return $Ref->newInstanceArgs($aData['params']);
        }
    }
}