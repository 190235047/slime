<?php
namespace Slime\Component\View;

use Psr\Log\LoggerInterface;

/**
 * Class View
 *
 * @package Slime\Component\View
 * @author  smallslime@gmail.com
 */
class Viewer
{
    public static function factory($sAdaptor, LoggerInterface $Log)
    {
        if ($sAdaptor[0] === '@') {
            $sAdaptor = '\\Slime\\Component\\View\\Adaptor_' . substr($sAdaptor, 1);
        }
        $Obj = new $sAdaptor($Log);
        if (!$Obj instanceof IAdaptor) {
            $Log->error('{adaptor} must impl Slime.Component.IAdaptor', array('adaptor' => $sAdaptor));
            exit(1);
        }
        return $Obj;
    }
}

