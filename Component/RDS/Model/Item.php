<?php
namespace SlimeFramework\Component\RDS;

class Model_Item implements \ArrayAccess
{
    private $aData;
    private $aOldData;
    private $aRelation;

    /** @var Model_Model */
    public $Model;

    public function __construct($aData, Model_Model $Model)
    {
        $this->aData = $aData;
        $this->Model = $Model;
    }

    public function __get($sKey)
    {
        return $this->aData[$sKey];
    }

    public function __set($sKey, $mValue)
    {
        $this->aOldData[$sKey] = $this->aData[$sKey];
        $this->aData[$sKey] = $mValue;
    }

    /**
     * @param string $sModelName
     * @return $this
     */
    public function relation($sModelName)
    {
        if (!isset($this->aRelation[$sModelName])) {
            $this->aRelation[$sModelName] = $this->Model->relation($sModelName, $this);
        }
        return $this->aRelation[$sModelName];
    }


    public function save()
    {
        if (isset($this->aData[$this->Model->sPK])) {
            $bRS = $this->Model->update($this->aData[$this->Model->sPK], $this->aData);
        } else {
            $iID = $this->Model->add($this->aData);
            if ($iID===null) {
                $bRS = false;
            } else {
                $this->aData[$this->Model->sPK] = $iID;
                $bRS = true;
            }
        }
        if ($bRS) {
            $this->aOldData = array();
        }
        return $bRS;
    }

    public function __toString()
    {
        return var_export($this->aData, true);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->aData);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->aData[$offset];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->aData[$offset] = $value;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->aData[$offset]);
    }
}