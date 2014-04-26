<?php
namespace Slime\Component\Memcached;

use Slime\Component\Context\Event;

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
    }

    public function __call($sMethodName, $aArg)
    {
        Event::occurEvent(Event_Register::E_ALL_BEFORE, $this, $sMethodName, $aArg);
        $mResult = empty($aArg) ?
            $this->getInstance()->$sMethodName() :
            call_user_func_array(array($this->getInstance(), $sMethodName), $aArg);
        Event::occurEvent(Event_Register::E_ALL_AFTER, $mResult, $this, $sMethodName, $aArg);
        return $mResult;
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