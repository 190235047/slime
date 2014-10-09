<?php
namespace Slime\Component\Cache;

use Slime\Ext\Sugar;

/**
 * Class Cache
 *
 * @package Slime\Component\Cache
 * @author  smallslime@gmail.com
 */
final class Cache
{
    /**
     * @param string $sAdaptor
     *
     * @return IAdaptor
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public static function factory($sAdaptor)
    {
        return Sugar::createObjAdaptor(__NAMESPACE__, func_get_args());
    }
}