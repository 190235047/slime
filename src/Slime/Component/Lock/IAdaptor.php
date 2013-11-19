<?php
namespace Slime\Component\Lock;

/**
 * Interface IAdaptor
 *
 * @package Slime\Component\Lock
 * @author  smallslime@gmail.com
 */
interface IAdaptor
{
    /**
     * @param string $sKey
     * @param int    $iExpire   0:永不过期
     * @param int    $iTimeout  0:永不超时(一直阻塞); -1:异步(发现阻塞不等待立刻返回false)
     *
     * @return bool
     */
    public function acquire($sKey, $iExpire, $iTimeout = -1);

    /**
     * @param string $sKey
     *
     * @return bool
     */
    public function release($sKey);
}