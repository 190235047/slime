<?php
namespace Slime\Component\Lock;

use Psr\Log\LoggerInterface;
use Slime\Component\Redis;

/**
 * Class Adaptor_Redis
 *
 * @package Slime\Component\Lock
 * @author  smallslime@gmail.com
 */
class Adaptor_Redis implements IAdaptor
{
    /** @var array */
    public $aConfig;

    /** @var \Redis */
    private $Redis;

    /**
     * @param Redis\Redis $Redis
     */
    public function __construct(Redis\Redis $Redis)
    {
        $this->Redis = $Redis;
    }


    /**
     * @param string $sKey
     * @param int    $iExpire      0:永不过期
     * @param int    $iTimeout     0:永不超时(一直阻塞); -1:异步(发现阻塞不等待立刻返回false)
     * @param bool   $bTimeoutAsMS false: 秒; true: 毫秒
     *
     * @return bool
     */
    public function acquire($sKey, $iExpire, $iTimeout = -1, $bTimeoutAsMS = false)
    {
        if ($iTimeout < 0) {
            $bRS = $this->Redis->setnx($sKey, 1);
        } else {
            $iT1 = microtime(true);
            do {
                $bRS = $this->Redis->setnx($sKey, 1);
                if ($bRS || ($iTimeout > 0 && microtime(true) - $iT1 > $iTimeout)) {
                    break;
                }
                usleep(10000);
            } while (true);
        }
        if ($iExpire !== 0 && $bRS) {
            $bTimeoutAsMS ? $this->Redis->pExpire($sKey, $iExpire) : $this->Redis->expire($sKey, $iExpire);
        }
        return $bRS;
    }

    /**
     * @param string $sKey
     *
     * @return bool
     */
    public function release($sKey)
    {
        return $this->Redis->del($sKey);
    }
}