<?php
namespace Slime\Component\Config;

/**
 * Interface IAdaptor
 *
 * @package Slime\Component\Config
 * @author  smallslime@gmail.com
 */
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

    /**
     * @param bool $bParse
     *
     * @return $this
     */
    public function setParseMode($bParse = true);

    /**
     * @param bool $bParse
     *
     * @return $this
     */
    public function setTmpParseMode($bParse = false);

    /**
     * @return $this
     */
    public function resetParseMode();
}