<?php
namespace SlimeFramework\Component\RDS;

use Psr\Log\LoggerInterface;

class Model_Group implements \ArrayAccess, \Iterator
{
    public function __construct(Model_Model $Model, LoggerInterface $Log)
    {
        $this->Model     = $Model;
        $this->Log       = $Log;
        $this->iCursor   = 0;
        $this->aaData    = array();
        $this->aPK       = array();
        $this->aRelation = array();
    }

    public function relation($sModelName, Model_Item $ModelItem = null)
    {
        if (!array_key_exists($sModelName, $this->aRelation) && !empty($this->aPK)) {
            $aRelConf = $this->Model->aRelConf;
            if (!isset($aRelConf[$sModelName])) {
                $this->Log->error('Relation model {model} is not exist', array('model' => $sModelName));
                exit(1);
            }
            $sMethod                      = $aRelConf[$sModelName];
            $this->aRelation[$sModelName] = $this->$sMethod($sModelName, $ModelItem);
        }

        if ($ModelItem===null) {
            return null;
        } else {
            return isset($this->aRelation[$sModelName][$ModelItem[$this->Model->sPKName]]) ?
                $this->aRelation[$sModelName][$ModelItem[$this->Model->sPKName]] : null;
        }
    }

    /**
     * @param $ModelName
     * @return Model_Group
     */
    public function hasOne($ModelName)
    {
        $Model = $this->Model->Pool->get($ModelName);
        return $Model->findMulti(array($this->Model->sFKName . ' IN' => $this->aPK));
    }

    /**
     * @param $sModel
     * @return Model_Group
     */
    public function belongsTo($sModel)
    {
        $Model = $this->Model->Pool->get($sModel);
        return $Model->findMulti(array($Model->sPKName . ' IN' => $this->aPK));
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
     * @return mixed Can return any type.
     */
    public function current()
    {
        return current($this->aaData);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->iCursor++;
        next($this->aaData);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return key($this->aaData);
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
        return $this->iCursor < count($this->aaData);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->iCursor = 0;
        reset($this->aaData);
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
        return isset($this->aaData[$offset]);
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
        return $this->aaData[$offset];
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
        $this->aPK[$value[$this->sPK]] = $this->sPK;
        $this->aaData[$offset]         = $value;
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
        unset($this->aPK[$this->aaData[$offset][$this->sPK]]);
        unset($this->aaData[$offset]);
    }

    public function __toString()
    {
        $sStr = '';
        foreach ($this->aaData as $Item) {
            $sStr .= (string)$Item . "\n";
        }
        return $sStr;
    }
}