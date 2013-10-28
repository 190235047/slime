<?php
namespace Slime\Component\DataStructure\Stack;

class Stack implements \Countable
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

    /**
     * @return mixed
     */
    public function pop()
    {
        $mItem = array_pop($this->aData);
        end($this->aData);
        return $mItem;
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