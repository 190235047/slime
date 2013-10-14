<?php
namespace Slime\Component\DataStructure\Queue;

class Queue implements \Countable
{
    /**
     * @var array
     */
    protected $aData;

    /**
     * @param array $aData
     */
    public function __construct(array $aData = array())
    {
        $this->aData = $aData;
    }

    /**
     * @param mixed $mV
     */
    public function add($mV)
    {
        if (empty($this->aData)) {
            $this->aData[] = $mV;
            reset($this->aData);
        } else {
            $this->aData[] = $mV;
        }
    }

    /**
     * @return mixed
     */
    public function get()
    {
        return array_shift($this->aData);
    }

    /**
     * @return mixed
     */
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