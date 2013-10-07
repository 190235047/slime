<?php
namespace SlimeFramework\Component\DataStructure\Queue;

class Queue implements \Countable
{
    protected $aData;

    public function add($mV)
    {
        if (empty($this->aData)) {
            $this->aData[] = $mV;
            reset($this->aData);
        } else {
            $this->aData[] = $mV;
        }
    }

    public function get()
    {
        return array_shift($this->aData);
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