<?php
namespace Slime\Component\Memcached;

/**
 * Class PHPMemcached
 *
 * @package Slime\Component\Memcached
 * @author  smallslime@gmail.com
 */
class PHPMemcached
{
    /**
     * @var \Memcached
     */
    private $Instance = null;

    public function __construct(array $aConfig)
    {
        $this->aConfig = $aConfig;
        //check config
    }

    public function __call($sMethodName, $aArg)
    {
        return call_user_func_array(array($this->getInstance(), $sMethodName), $aArg);
    }

    /**
     * @return \Memcached
     */
    public function getInstance()
    {
        if ($this->Instance === null) {
            $this->Instance = new \Memcached();
            $this->Instance->addServers($this->aConfig['servers']);
        }
        return $this->Instance;
    }
}