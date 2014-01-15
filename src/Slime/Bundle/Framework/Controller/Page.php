<?php
namespace Slime\Bundle\Framework;

use Slime\Component\Http;
use Slime\Component\View;

/**
 * Class Controller_Page
 * Slime 内置 Page 控制器基类
 * 建议 Autoload View Module
 *
 * @package Slime\Bundle\Framework
 * @author  smallslime@gmail.com
 */
abstract class Controller_Page extends Controller_ABS
{
    # render type
    const RENDER_NONE = -1;
    const RENDER_AUTO = 0;
    const RENDER_PAGE = 1;
    const RENDER_JUMP = 2;

    # is http method get
    protected $bGet            = true;

    # is ajax
    protected $bAjax           = false;

    # for render
    protected $sTPL            = null;
    protected $aData           = array();

    # for jump
    protected $sJumpUrl        = null;
    protected $iJumpCode       = null;

    # render/jump logic if get
    private $iRenderType       = self::RENDER_AUTO;


    public function __construct(array $aParam = array())
    {
        parent::__construct($aParam);

        $this->HttpRequest   = $this->Context->HttpRequest;
        $this->HttpResponse  = $this->Context->HttpResponse;

        $this->bGet          = $this->HttpRequest->getRequestMethod()==='GET';
        $this->bAjax         = $this->HttpRequest->isAjax();
    }

    /**
     * 主逻辑完成后运行
     */
    public function __after__()
    {
        if ($this->iRenderType === self::RENDER_NONE) {
            return;
        }

        if ($this->iRenderType === self::RENDER_AUTO) {
            $this->iRenderType = ($this->bGet || $this->bAjax) ? self::RENDER_PAGE : self::RENDER_JUMP;
        }

        if ($this->iRenderType === self::RENDER_PAGE) {
            $this->HttpResponse->setContent(
                ltrim(
                    $this->Context->View
                        ->assignMulti($this->aData)
                        ->setTpl($this->sTPL === null ? $this->getDefaultTPL() : $this->sTPL)
                        ->renderAsResult()
                )
            );
        } else {
            $sJump = $this->sJumpUrl === null ? $this->HttpRequest->getHeader('Referer') : $this->sJumpUrl;
            $this->HttpResponse->setRedirect($sJump === null ? '/' : $sJump, $this->iJumpCode);
        }
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
        $sExt = $this->getParam('__ext__', 'html');
        $sExt = $sExt === 'html' ? '' : ".$sExt";
        return "{$sTPL}{$sExt}.php";
    }

    protected function setRenderMode_Auto()
    {
        $this->iRenderType = self::RENDER_AUTO;
    }

    protected function setRenderMode_Page()
    {
        $this->iRenderType = self::RENDER_PAGE;
    }

    protected function setRenderMode_Jump()
    {
        $this->iRenderType = self::RENDER_JUMP;
    }

    protected function setRenderMode_None()
    {
        $this->iRenderType = self::RENDER_NONE;
    }

    /**
     * @return int RENDER_NONE/RENDER_PAGE/RENDER_JUMP
     */
    protected function getRenderType()
    {
        return $this->iRenderType === self::RENDER_AUTO ?
            (($this->bGet || $this->bAjax) ? self::RENDER_PAGE : self::RENDER_JUMP):
            $this->iRenderType;
    }
}