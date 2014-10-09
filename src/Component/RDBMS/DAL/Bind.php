<?php
namespace Slime\Component\RDBMS\DAL;

/**
 * Class Bind
 *
 * @package Slime\Component\RDBMS\DAL
 * @author  smallslime@gmail.com
 */
class Bind implements \ArrayAccess,\Countable
{
    protected $aData = array();

    /**
     * @param string $sK
     * @param mixed  $mV string | int | float | Object:Val
     * @param int    $iType
     *
     * @return $this
     */
    public function set($sK, $mV, $iType = \PDO::PARAM_STR)
    {
        $this->aData[$sK] = array(Val::ValPre($sK), $mV, $iType);
        return $this;
    }

    /**
     * @param array $aArr array value as function set [$sK, $mV, $iType]
     *
     * @return $this
     */
    public function setMulti($aArr)
    {
        foreach ($aArr as $aRow) {
            $this->aData[$aRow[0]] = array($aRow[1], isset($aRow[2]) ? $aRow[2] : \PDO::PARAM_STR);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->aData;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return isset($this->aData[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        if (!isset($this->aData[$offset])) {
            throw new \DomainException("Key[$offset] is not exists in data");
        }
        return $this->aData[$offset][0];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     *
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     *       </p>
     *       <p>
     *       The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->aData);
    }
}
