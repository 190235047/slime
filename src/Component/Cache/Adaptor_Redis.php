<?php
namespace Slime\Component\Cache;

/**
 * Class Adaptor_Redis
 *
 * @package Slime\Component\Cache
 * @author  smallslime@gmail.com
 */
class Adaptor_Redis implements IAdaptor
{
    /** @var array */
    public $aConfig;

    /** @var \Redis */
    private $Obj;

    /**
     * @param \Slime\Component\Redis\Redis $Redis
     */
    public function __construct($Redis)
    {
        $this->Obj = $Redis;
    }

    public function __call($sMethod, $aParam)
    {
        return empty($aParam) ? $this->Obj->$sMethod() : call_user_func_array(array($this->Obj, $sMethod), $aParam);
    }

    /**
     * @param string $sKey
     *
     * @return mixed
     */
    public function get($sKey)
    {
        return $this->Obj->get($sKey);
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
        return $this->Obj->set($sKey, $mValue, $iExpire);
    }

    /**
     * @param $sKey
     *
     * @return bool
     */
    public function delete($sKey)
    {
        return $this->Obj->delete($sKey);
    }

    /**
     * @return bool
     */
    public function flush()
    {
        return $this->Obj->flushDB();
    }
}