<?php
namespace SlimeFramework\Component\RDS;

class Model_Item implements \ArrayAccess
{
    private $aData;
    private $aOldData;
    private $aRelation;

    /** @var Model_Model */
    public $Model;

    /** @var Model_Group|null */
    public $Group;

    public function __construct($aData, Model_Model $Model, $Group = null)
    {
        $this->aData = $aData;
        $this->Model = $Model;
        $this->Group = $Group;
    }

    public function __get($sKey)
    {
        return $this->aData[$sKey];
    }

    public function __set($sKey, $mValue)
    {
        $this->aOldData[$sKey] = $this->aData[$sKey];
        $this->aData[$sKey]    = $mValue;
    }

    public function set($mKeyOrKVMap, $mValue = null)
    {
        if (!is_array($mKeyOrKVMap)) {
            $this->aOldData[$mKeyOrKVMap] = $this->aData[$mKeyOrKVMap];
            $this->aData[$mKeyOrKVMap]    = $mValue;
        } else {
            foreach ($mKeyOrKVMap as $sKey => $mValue) {
                $this->aOldData[$sKey] = $this->aData[$sKey];
                $this->aData[$sKey]    = $mValue;
            }
        }
        return $this;
    }

    /**
     * @param string $sModelName
     * @param array  $mValue
     *
     * @return $this|null
     */
    public function __call($sModelName, $mValue = array())
    {
        if (!isset($this->Model->aRelConf[$sModelName])) {
            $this->Model->Log->error('can not find ralation for [{model}]', array('model' => $sModelName));
            exit(1);
        }
        $sRelation = strtolower($this->Model->aRelConf[$sModelName]);
        if (
            ($sRelation !== 'hasone' && $sRelation !== 'belongsto') ||
            $this->Group === null || empty($mValue[0])
        ) {
            if (!isset($this->aRelation[$sModelName])) {
                $this->aRelation[$sModelName] = $this->Model->relation($sModelName, $this);
            }
            return $this->aRelation[$sModelName];
        } else {
            return $this->Group->relation($sModelName, $this);
        }
    }

    public function save()
    {
        if (isset($this->aData[$this->Model->sPKName])) {
            $bRS = $this->Model->update(
                $this->aData[$this->Model->sPKName],
                array_intersect_key($this->aData, $this->aOldData)
            );
            if ($bRS) {
                $this->aOldData = array();
            }
        } else {
            $iID = $this->Model->add($this->aData);
            if ($iID === null) {
                $bRS = false;
            } else {
                $this->aData[$this->Model->sPKName] = $iID;
                $bRS                                = true;
            }
        }
        return $bRS;
    }

    public function delete()
    {
        return $this->Model->delete($this->aData[$this->Model->sPKName]);
    }

    public function toArray()
    {
        return $this->aData;
    }

    public function __toString()
    {
        return var_export($this->aData, true);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)
     * Whether a offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset An offset to check for.
     *
     * @return boolean true on success or false on failure.
     *       The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->aData);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)
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
        return $this->aData[$offset];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)
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
     * (PHP 5 &gt;= 5.0.0)
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
        unset($this->aData[$offset]);
    }
}