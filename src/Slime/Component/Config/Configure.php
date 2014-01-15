<?php
namespace Slime\Component\Config;

use Slime\Bundle\Framework\Context;
use Slime\Component\Helper\Sugar;

/**
 * Class Configure
 *
 * @package Slime\Component\Config
 * @author  smallslime@gmail.com
 */
final class Configure
{
    /**
     * @param string $sAdaptor
     *
     * @throws \Exception
     * @return IAdaptor
     */
    public static function factory($sAdaptor)
    {
        return Sugar::createObjAdaptor(__NAMESPACE__, func_get_args());
    }

    public static function parseRecursion($mResult, IAdaptor $Config)
    {
        if (is_string($mResult)) {
            switch ($mResult[0]) {
                case '@':
                    $mResult = $Config->get(substr($mResult, 1));
                    break;
                case ':':
                    $sModuleName = substr($mResult, 1);
                    $mResult     = Context::getInst()->$sModuleName;
                    break;
                case '\\':
                    $mResult = substr($mResult, 1);
                    break;
            }
        } elseif (is_array($mResult)) {
            foreach ($mResult as $mK => $mV) {
                $mResult[$mK] = self::parseRecursion($mV, $Config);
            }
        }
        return $mResult;
    }
}