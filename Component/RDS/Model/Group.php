<?php
namespace SlimeFramework\Component\RDS;

use Psr\Log\LoggerInterface;

class Model_Group implements \ArrayAccess, \Iterator, \Countable
{
    /** @var Model_Item[] */
    public $aModelItem;

    public function __construct(Model_Model $Model, LoggerInterface $Log)
    {
        $this->Model      = $Model;
        $this->Log        = $Log;
        $this->aModelItem = array();
        $this->aaDataMap  = array();
        $this->aRelation  = array();
        $this->aRelObj    = array();
    }

    public function relation($sModelName, Model_Item $ModelItem = null)
    {
        $aRelConf = $this->Model->aRelConf;
        if (!isset($aRelConf[$sModelName])) {
            $this->Log->error('Relation model {model} is not exist', array('model' => $sModelName));
            exit(1);
        }
        $sMethod = $aRelConf[$sModelName];
        return $this->$sMethod($sModelName, $ModelItem);
    }

    /**
     * @param $sModelName
     * @param Model_Item $ModelItem
     * @return Model_Group | Model_Item | null
     */
    public function hasOne($sModelName, Model_Item $ModelItem = null)
    {
        if ($ModelItem === null && isset($this->aRelation[$sModelName])) {
            return $this->aRelation[$sModelName];
        }

        $sPK = $ModelItem[$this->Model->sPKName];
        if (isset($this->aRelObj[$sModelName][$sPK])) {
            return $this->aRelObj[$sModelName][$sPK];
        }

        $aPK                          = array_keys($this->aModelItem);
        $Model                        = $this->Model->Pool->get($sModelName);
        $this->aRelation[$sModelName] = $Group = $Model->findMulti(array($this->Model->sFKName . ' IN' => $aPK));

        if ($ModelItem === null) {
            return $Group;
        }

        $this->aRelObj[$sModelName] = array();
        $aQ = &$this->aRelObj[$sModelName];
        foreach ($Group as $iID => $ItemNew) {
            $sThisPK = $this->aModelItem[$ItemNew[$this->Model->sFKName]][$this->Model->sPKName];
            $aQ[$sThisPK] = $iID;
        }

        return $aQ[$sPK];
    }

    /**
     * @param $sModelName
     * @param Model_Item $ModelItem
     * @return Model_Group | Model_Item | null
     */
    public function belongsTo($sModelName, Model_Item $ModelItem = null)
    {
        if ($ModelItem === null && isset($this->aRelation[$sModelName])) {
            return $this->aRelation[$sModelName];
        } else {
            $Model = $this->Model->Pool->get($sModelName);
            $sFK   = $ModelItem[$Model->sFKName];
            if (isset($this->aRelation[$sModelName][$sFK])) {
                return $this->aRelation[$sModelName][$sFK];
            }
        }

        $aFK = array();
        foreach ($this->aModelItem as $Item) {
            $aFK[] = $Item[$Model->sFKName];
        }
        $this->aRelation[$sModelName] = $Model->findMulti(array($Model->sPKName . ' IN' => $aFK));
        if ($ModelItem === null) {
            return $this->aRelation[$sModelName];
        } else {
            return $this->aRelation[$sModelName][$sFK];
        }
    }

    public function hasMany($sModel)
    {
        //@todo
    }

    public function hasManyThough($sModel, $mPKOrWhere = null)
    {
        //@todo
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return Model_Item Can return any type.
     */
    public function current()
    {
        return $this->aModelItem[current($this->aaDataMap)];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        next($this->aaDataMap);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return current($this->aaDataMap);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return current($this->aaDataMap) !== false;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        reset($this->aaDataMap);
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
        return isset($this->aModelItem[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return Model_Item Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->aModelItem[$offset];
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
        $this->aaDataMap[$value[$this->Model->sPKName]] = $value[$this->Model->sPKName];
        $this->aModelItem[$offset]                          = $value;
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
        unset($this->aaDataMap[$offset]);
        unset($this->aModelItem[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->aModelItem);
    }

    public function __toString()
    {
        $sStr = '';
        foreach ($this->aModelItem as $Item) {
            $sStr .= (string)$Item . "\n";
        }
        return $sStr;
    }
}