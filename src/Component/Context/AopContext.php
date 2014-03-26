<?php
namespace Slime\Component\Context;

use Slime\Bundle\Framework\Context as C;
use Slime\Component\Log\Logger;


/**
 * Class AopPDO
 *
 * @package Slime\Component\RDS
 * @author  smallslime@gmail.com
 */
class AopContext
{
    public static $aAopCTXRegDebug = array(
        'prepare.renderAsResult' => array(
            array('Slime\Component\Context\AopContext', 'registerBefore')
        )
    );

    private static $aCacheData = array();

    public static function registerBefore($Obj, $sMethod, array $aArgv, \stdClass $Result)
    {
        $C    = C::getInst();
        $sStr = sprintf('Context Reg: %s', $aArgv[0]);
        if (!$C->isRegistered('Log')) {
            self::$aCacheData[] = $sStr;
        } else {
            $Log = $C->Log;
            if ($Log->needLog(Logger::LEVEL_DEBUG)) {
                if (!empty(self::$aCacheData)) {
                    foreach (self::$aCacheData as $sRow) {
                        $C->Log->debug($sRow);
                    }
                    self::$aCacheData = array();
                }
                $C->Log->debug($sStr);
            }
        }
    }
}