<?php
namespace Slime\Component\Http;

/**
 * Class Bag_Base
 *
 * @package Slime\Component\Http
 * @author  smallslime@gmail.com
 *
 * @property-read array $aData
 */
class Bag_Base implements \ArrayAccess, \Countable
{
    protected $aData;

    /**
     * @param array $aData
     */
    public function __construct(array $aData = array())
    {
        $this->aData = $aData;
    }

    public function getData()
    {
        return $this->aData;
    }

    public function __get($sKey)
    {
        return $this->offsetGet($sKey);
    }

    /**
     * @param string $sK
     * @param string $mV
     * @param bool   $bOverwrite
     */
    public function set($sK, $mV, $bOverwrite = true)
    {
        if ($bOverwrite || !isset($this->aData[$sK])) {
            $this->aData[$sK] = $mV;
        }
    }

    /**
     * @param array $aKV
     * @param bool  $bOverwrite
     */
    public function setMulti($aKV, $bOverwrite = true)
    {
        $this->aData = $bOverwrite ?
            array_replace($this->aData, $aKV) :
            array_merge($aKV, $this->aData);
    }

    /**
     * (PHP 5 >= 5.0.0)
     * Whether a offset exists
     *
     * @link   http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset An offset to check for.
     *
     * @return boolean true on success or false on failure.
     *         The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->aData);
    }

    /**
     * (PHP 5 >= 5.0.0)
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return isset($this->aData[$offset]) ? $this->aData[$offset] : null;
    }

    /**
     * (PHP 5 >= 5.0.0)
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value  The value to set.
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->aData[$offset] = $value;
    }

    /**
     * (PHP 5 >= 5.0.0)
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset The offset to unset.
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        if (isset($this->aData[$offset])) {
            unset($this->aData[$offset]);
        }
    }

    /**
     * (PHP 5 >= 5.1.0)<br/>
     * Count elements of an object
     *
     * @link   http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     *         The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->aData);
    }

    public function __toString()
    {
        return var_export($this->aData, true);
    }
}