<?php
namespace Slime\Component\Cache;

use Psr\Log\LoggerInterface;

class Adaptor_Redis implements IAdaptor
{
    /** @var array */
    public $aConfig;

    /** @var \Redis */
    private $Redis;

    /**
     * @param array           $aConfig
     * @param LoggerInterface $Logger
     */
    public function __construct(array $aConfig, LoggerInterface $Logger)
    {
        $this->aConfig = $aConfig;
        $this->Logger  = $Logger;
    }

    /**
     * @param string $sKey
     *
     * @return mixed
     */
    public function get($sKey)
    {
        return $this->getInstance()->get($sKey);
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
        return $this->getInstance()->set($sKey, $mValue, $iExpire);
    }

    /**
     * @param $sKey
     *
     * @return bool
     */
    public function delete($sKey)
    {
        return $this->getInstance()->delete($sKey);
    }

    /**
     * @return bool
     */
    public function flush()
    {
        return $this->getInstance()->flush();
    }

    /**
     * @return $this
     */
    public function getInstance()
    {
        if (!$this->Redis) {
            if ($this->aConfig['type'] == 'single') {
                $this->Redis = new \Redis();
                call_user_func_array(
                    array($this->Redis, $this->aConfig['pconnect'] ? 'pconnect' : 'connect'),
                    $this->aConfig['config']
                );
            } else {
                $Ref         = new \ReflectionClass('\Redis');
                $this->Redis = $Ref->newInstanceArgs($this->aConfig['config']);
            }
            if (!empty($this->aConfig['option'])) {
                foreach ($this->aConfig['option'] as $mK => $mV) {
                    $this->Redis->setOption($mK, $mV);
                }
            }
        }
        return $this->Redis;
    }
}