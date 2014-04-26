<?php
namespace Slime\Component\Redis;

use Slime\Component\Context\Event;

/**
 * Class RedisWR
 *
 * @package Slime\Component\Redis
 * @author  smallslime@gmail.com
 */
class RedisWR
{
    const REDIS_WRITE = 1;
    const REDIS_READ  = 2;

    /**
     * @var \Redis[]
     */
    private $aRedis;

    /**
     * @var array
     */
    protected $aConfig;

    protected $niType = null;

    /**
     * @param array $aConfig
     */
    public function __construct(array $aConfig)
    {
        $this->aConfig = $aConfig;
    }

    public function __call($sMethod, $aArgs)
    {
        Event::occurEvent(Event_Register::E_ALL_BEFORE, $this, $sMethod, $aArgs);
        $mRS = call_user_func_array(array($this->getInstance($sMethod), $sMethod), $aArgs);
        Event::occurEvent(Event_Register::E_ALL_AFTER, $mRS, $this, $sMethod, $aArgs);
        return $mRS;
    }

    public function getInstance($sMethod)
    {
        if (empty($this->aRedis)) {
            if (count($this->aConfig)!==2) {
                throw new \RuntimeException("RedisWR config must has two items with write and read instance");
            }
            foreach ($this->aConfig as $iWR => $aItem) {
                if ($aItem['type'] == 'single') {
                    $Redis = new \Redis();
                    call_user_func_array(
                        array($aItem, !empty($aItem['pconnect']) ? 'pconnect' : 'connect'),
                        $aItem['server']
                    );
                } else {
                    $Ref   = new \ReflectionClass('\RedisArray');
                    $Redis = $Ref->newInstance($this->aConfig['servers']);
                }
                if (!empty($aItem['options'])) {
                    foreach ($aItem['options'] as $mK => $mV) {
                        $Redis->setOption($mK, $mV);
                    }
                }
                if (!empty($aConfig['db'])) {
                    $Redis->select($this->aConfig['db']);
                }
                $this->aRedis[$iWR==self::REDIS_WRITE ? self::REDIS_WRITE : self::REDIS_READ] = $Redis;
            }
        }
        return $this->judgeInstance($sMethod);
    }

    public function setWrite()
    {
        $this->niType = self::REDIS_WRITE;
    }

    public function setRead()
    {
        $this->niType = self::REDIS_READ;
    }

    public function setAuto()
    {
        $this->niType = null;
    }

    public static $aReadCMD = array(
        'EXISTS'           => true,
        'GET'              => true,
        'GETBIT'           => true,
        'GETRANGE'         => true,
        'HGET'             => true,
        'HGETALL'          => true,
        'HKEYS'            => true,
        'HLEN'             => true,
        'HMGET'            => true,
        'HVALS'            => true,
        'INFO'             => true,
        'KEYS'             => true,
        'LINDEX'           => true,
        'LLEN'             => true,
        'LRANGE'           => true,
        'MGET'             => true,
        'PTTL  '           => true,
        'SCARD'            => true,
        'SISMEMBER'        => true,
        'SMEMBERS'         => true,
        'SRANDMEMBER'      => true,
        'STRLEN'           => true,
        'TTL'              => true,
        'TYPE'             => true,
        'ZCARD'            => true,
        'ZCOUNT'           => true,
        'ZLEXCOUNT'        => true,
        'ZRANGE'           => true,
        'ZRANGEBYLEX'      => true,
        'ZRANGEBYSCORE'    => true,
        'ZRANK'            => true,
        'ZREVRANGE'        => true,
        'ZREVRANGEBYSCORE' => true,
        'ZREVRANK'         => true,
        'ZSCORE'           => true,
        'SCAN'             => true,
        'SSCAN'            => true,
        'HSCAN'            => true,
        'ZSCAN'            => true,
    );

    protected function judgeInstance($sMethod)
    {
        if ($this->niType !== null) {
            return $this->aRedis[$this->niType];
        } else {
            return empty(self::$aReadCMD[strtoupper($sMethod)]) ?
                $this->aRedis[self::REDIS_WRITE] :
                $this->aRedis[self::REDIS_READ];
        }
    }
}
