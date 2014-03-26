<?php
namespace Slime\Component\View;

use Slime\Bundle\Framework\Context;
use Slime\Component\Log\Logger;

/**
 * Class AopPDO
 *
 * @package Slime\Component\RDS
 * @author  smallslime@gmail.com
 */
class AopView
{
    public static $aAopHttpCost = array(
        'prepare.renderAsResult' => array(
            array('Slime\Component\RDS\AopView', 'TplBefore')
        )
    );

    public static function tplBefore(IAdaptor $Obj, $sMethod, array $aArgv, \stdClass $Result)
    {
        $Log = Context::getInst()->Log;
        if ($Log->needLog(Logger::LEVEL_DEBUG)) {
            $Log->debug(
                'TPL : {path}',
                array('path' => $Obj->getBaseDir() . DIRECTORY_SEPARATOR . $Obj->getTpl())
            );
        }
    }
}