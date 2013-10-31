<?php
namespace Slime\Component\Cache;

use Slime\Component\Memcached;

class Adaptor_Memcached implements IAdaptor
{
    /** @var \Memcached */
    public $Obj;

    public function __construct(Memcached\PHPMemcached $Memcached)
    {
        $this->Obj = $Memcached;
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
        return $this->Obj->flush();
    }
}