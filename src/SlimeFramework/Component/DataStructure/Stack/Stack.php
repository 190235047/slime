<?php
namespace SlimeFramework\Component\DataStructure\Stack;

class Stack implements \Countable
{
    protected $aData = array();

    public function push($mV)
    {
        if (empty($this->aData)) {
            $this->aData[] = $mV;
            reset($this->aData);
        } else {
            $this->aData[] = $mV;
            next($this->aData);
        }
    }

    public function pop()
    {
        $mV = array_pop($this->aData);
        if ($mV!==null) {
            prev($this->aData);
        }
        return $mV;
    }

    public function current()
    {
        return current($this->aData);
    }

    /**
     * (PHP 5 >= 5.1.0)
     * Count elements of an object
     *
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     *         The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->aData);
    }
}