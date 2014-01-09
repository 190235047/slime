<?php
namespace Slime\Bundle\Framework;

use Slime\Component\Http;
use Slime\Component\View;

/**
 * Class Controller_Page
 * Slime 内置 Page 控制器基类
 *
 * @package Slime\Bundle\Framework
 * @author  smallslime@gmail.com
 */
abstract class Controller_Page extends Controller_ABS
{
    protected $sRedirectURL = null;
    protected $sTPL         = null;
    protected $aData        = array();

    public function __construct(array $aParam = array())
    {
        parent::__construct($aParam);

        $this->HttpRequest   = $this->Context->HttpRequest;
        $this->HttpResponse  = $this->Context->HttpResponse;
    }

    /**
     * 主逻辑完成后运行
     */
    public function __after__()
    {
        $sTPL = $this->sTPL === null ? $this->getDefaultTPL() : $this->sTPL;

        if ($this->HttpRequest->getRequestMethod()==='POST' && $sTPL===null) {
            $sRedirectURL = $this->sRedirectURL === null ?
                $this->HttpRequest->getHeader('Referer') :
                $this->sRedirectURL;
            if ($sRedirectURL === null) {
                $sRedirectURL = '/';
            }
            $this->HttpResponse->setRedirect($sRedirectURL);
            return;
        }

        $View = View\Viewer::factory('@PHP')
            ->setBaseDir($this->Context->aAppDir['view'])
            ->setTpl($this->sTPL)
            ->assignMulti($this->aData);
        $this->Context->register('View', $View);
        $this->HttpResponse->setContent(ltrim($View->renderAsResult()));
    }

    protected function getDefaultTPL()
    {
        $CB        = $this->Context->CallBack;
        $aCallable = $CB->mCallable;
        $sTPL      = str_replace(
            '_',
            DIRECTORY_SEPARATOR,
            str_replace($CB->sNSPre . '\ControllerHttp_', '', get_class($aCallable[0]))
        );
        $sMethod   = substr($aCallable[1], 6);
        if ($sMethod !== 'Default') {
            $sTPL .= "_$sMethod";
        }
        return $sTPL . '.php';
    }
}