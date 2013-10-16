<?php
namespace Slime\Component\Config;

interface IAdaptor
{
    /**
     * @param string $sKey
     * @param mixed  $mDefaultValue
     * @param bool   $bForce
     *
     * @return mixed
     */
    public function get($sKey, $mDefaultValue = null, $bForce = false);
}