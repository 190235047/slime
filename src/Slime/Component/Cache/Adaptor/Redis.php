<?php
namespace Slime\Component\Cache;

use Psr\Log\LoggerInterface;
use Slime\Component\Redis\Redis;

class Adaptor_Redis implements IAdaptor
{
    /** @var array */
    public $aConfig;

    # this is a hack for code auto complete
    /** @var \Redis */
    private $Redis;

    /**
     * @param Redis           $Redis
     * @param LoggerInterface $Logger
     */
    public function __construct(Redis $Redis, LoggerInterface $Logger)
    {
        $this->Redis   = $Redis;
        $this->Logger  = $Logger;
    }

    /**
     * @param string $sKey
     *
     * @return mixed
     */
    public function get($sKey)
    {
        return $this->Redis->get($sKey);
    }

    /**
     * @param string $sKey
     * @param mixed  $mValue
     * @param int    $iExpire
     *
     * @return bool
     */
    public function set($sKey, $mValue, $iExpire)
    {
        return $this->Redis->set($sKey, $mValue, $iExpire);
    }

    /**
     * @param $sKey
     *
     * @return bool
     */
    public function delete($sKey)
    {
        return $this->Redis->delete($sKey);
    }

    /**
     * @return bool
     */
    public function flush()
    {
        return $this->Redis->flushDB();
    }
}