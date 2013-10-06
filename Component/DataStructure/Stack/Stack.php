<?php
namespace SlimeFramework\Component\DataStructure\Stack;

class Stack
{
    private $aData = array();

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

    public function getCurrent()
    {
        return current($this->aData);
    }
}