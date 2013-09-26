<?php
namespace SlimeFramework\Component\Config;

interface IAdaptor
{
    /**
     * @param string $sKey
     * @param mixed  $sDefaultValue
     * @param int    $iErrorLevel
     *
     * @return mixed
     */
    public function get($sKey, $sDefaultValue = null, $iErrorLevel = 0);
}