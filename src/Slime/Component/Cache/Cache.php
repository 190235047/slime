<?php
namespace Slime\Component\Cache;

use Slime\Component\Helper\Sugar;

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
     * @throws \Exception
     * @return IAdaptor
     */
    public static function factory($sAdaptor)
    {
        return Sugar::createObjAdaptor(__NAMESPACE__, func_get_args());
    }
}