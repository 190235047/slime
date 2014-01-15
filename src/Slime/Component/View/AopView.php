<?php
namespace Slime\Component\View;

use Slime\Bundle\Framework\Context;

/**
 * Class AopPDO
 *
 * @package Slime\Component\RDS
 * @author  smallslime@gmail.com
 */
class AopView
{
    public static function tplBefore(IAdaptor $Obj, $sMethod, array $aArgv, \ArrayObject $Result)
    {
        Context::getInst()->Log->debug('TPL : {path}', array('path' => $Obj->getBaseDir() . DIRECTORY_SEPARATOR . $Obj->getTpl()));
    }

    public static function getAopConf()
    {
        return array(
            'prepare.renderAsResult' => array(
                array('Slime\Component\RDS\AopView', 'TplBefore')
            )
        );
    }
}