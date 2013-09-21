<?php
namespace SlimeFramework\Component\View;

use Psr\Log\LoggerInterface;

/**
 * Class View
 * @package SlimeFramework\Component\View
 * @author smallslime@gmail.com
 */
class Viewer
{
    public static function factory($sAdaptor, LoggerInterface $Log)
    {
        if ($sAdaptor[0]==='@') {
            $sAdaptor = '\\SlimeFramework\\Component\\View\\Adaptor_' . substr($sAdaptor, 1);
        }
        $Obj = new $sAdaptor();
        if (!$Obj instanceof IAdaptor) {
            $Log->error('{adaptor} must impl SlimeFramework.Component.IAdaptor', array('adaptor' => $sAdaptor));
            exit(1);
        }
        return $Obj;
    }
}

