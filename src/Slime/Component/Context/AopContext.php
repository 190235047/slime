<?php
namespace Slime\Component\Context;

use Slime\Bundle\Framework\Context as C;


/**
 * Class AopPDO
 *
 * @package Slime\Component\RDS
 * @author  smallslime@gmail.com
 */
class AopContext
{
    private static $aCacheData = array();

    public static function registerBefore($Obj, $sMethod, array $aArgv, \ArrayObject $Result)
    {
        $C = C::getInst();
        $sStr = sprintf('Context Reg: %s', $aArgv[0]);
        if (!$C->isRegister('Log')) {
            self::$aCacheData[] = $sStr;
        } else {
            if (!empty(self::$aCacheData)) {
                foreach (self::$aCacheData as $sRow) {
                    $C->Log->debug($sRow);
                }
                self::$aCacheData = array();
            }
            $C->Log->debug($sStr);
        }
    }

    public static function getAopConf()
    {
        return array(
            'prepare.renderAsResult' => array(
                array('Slime\Component\Context\AopContext', 'registerBefore')
            )
        );
    }
}