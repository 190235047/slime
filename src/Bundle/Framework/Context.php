<?php
namespace Slime\Bundle\Framework;

/**
 * Class Context
 *
 * @package Slime\Bundle\Framework
 * @author  smallslime@gmail.com
 *
 * @property-read string                                     $sENV           当前环境(例如 publish:生产环境; development:开发环境)
 * @property-read string                                     $sRunMode       PHP运行方式, 当前支持 (cli||http)
 * @property-read string                                     $sControllerPre 当前控制器类前缀
 * @property-read string                                     $sActionPre     当前控制器方法前缀
 * @property-read Bootstrap                                  $Bootstrap      框架核心基础对象
 * @property-read \Slime\Component\Config\IAdaptor           $Config         配置对象
 * @property-read \Slime\Component\Log\Logger                $Log            日志对象
 * @property-read \Slime\Component\Context\Event             $Event          事件对象
 * @property-read \Slime\Component\Route\Router              $Route          路由对象
 * @property-read \Slime\Component\Route\CallBack            $CallBack       路由结果回调对象
 * @property-read \Slime\Component\HTTP\HttpRequest          $HttpRequest    本次Http请求生成的HttpRequest对象
 * @property-read \Slime\Component\HTTP\HttpResponse         $HttpResponse   响应本次Http请求的HttpResponse对象
 * @property-read array                                      $aArgv          本次CLI请求的参数数组
 * @property-read \Slime\Component\I18N\I18N                 $I18N           多语言对象
 * @property-read \Slime\Component\View\IAdaptor             $View           模板对象
 * @property-read mixed                                      $mCBErrPage     错误页回调
 * @property-read array                                      $Arr            自定义数组
 */
class Context extends \Slime\Component\Context\Context
{
}