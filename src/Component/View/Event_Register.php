<?php
namespace Slime\Component\View;

use Slime\Bundle\Framework\Context;
use Slime\Component\Context\Event;
use Slime\Component\Log\Logger;

/**
 * Class AopPDO
 *
 * @package Slime\Component\RDS
 * @author  smallslime@gmail.com
 */
class Event_Register
{
    const E_RENDER_RS_BEFORE = 'Slime.Component.View.IAdaptor.renderAsResult:before';

    public static function register_renderAsResult()
    {
        Event::regEvent(
            self::E_RENDER_RS_BEFORE,
            function(IAdaptor $View)
            {
                $Log = Context::getInst()->Log;
                if ($Log->needLog(Logger::LEVEL_DEBUG)) {
                    $Log->debug(
                        '[TPL] : {path}',
                        array('path' => $View->getBaseDir() . DIRECTORY_SEPARATOR . $View->getTpl())
                    );
                }
            }
        );
    }
}