<?php
namespace Slime\Component\View;

/**
 * Class Ext
 *
 * @package Slime\Component\View
 * @author  smallslime@gmail.com
 */
class Ext
{
    /**
     * @param \Slime\Component\Event\Event $EV
     * @param \Slime\Component\Log\Logger  $Log
     */
    public static function ev_LogPHPRender($EV, $Log)
    {
        $EV->listen(Adaptor_PHP::EV_RENDER,
            function ($Obj, $sMethod, $aArg, $Local) use ($Log) {
                $Log->info("[VIEW] ; Render view[{$Local['file']}]");
            }
        );
    }
}