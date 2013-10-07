<?php
namespace SlimeFramework\Component\Http;

class Bag_Bag implements \ArrayAccess, \Iterator, \Countable
{
    public function __construct(array $aData = null)
    {
        $this->aData   = $aData === null ? array() : $aData;
        $this->aMap    = array_keys($this->aData);
        $this->iLen    = count($aData);
        $this->iCursor = 0;
    }

    /**
     * (PHP 5 >= 5.0.0)
     * Return the current element
     *
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return $this->aData[$this->aMap[$this->iCursor]];
    }

    /**
     * (PHP 5 >= 5.0.0)
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->iCursor++;
    }

    /**
     * (PHP 5 >= 5.0.0)
     * Return the key of the current element
     *
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->aMap[$this->iCursor];
    }

    /**
     * (PHP 5 >= 5.0.0)
     * Checks if current position is valid
     *
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     *       Returns true on success or false on failure.
     */
    public function valid()
    {
        return $this->iCursor <= $this->iLen;
    }

    /**
     * (PHP 5 >= 5.0.0)
     * Rewind the Iterator to the first element
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->iCursor = 0;
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
        return array_key_exists($this->aData, $offset);
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
        $this->aMap[]         = $offset;
        $this->iLen++;
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
        unset($this->aData[$offset]);
        $this->aMap = array_keys($this->aData);
        $this->iLen--;
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
        return $this->iLen;
    }

    public function toArray()
    {
        return $this->aData;
    }
}