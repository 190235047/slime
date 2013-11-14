<?php
namespace Slime\Component\Context;

class DataContent
{
    public $mData;

    public function __construct(&$mData)
    {
        $this->mData = &$mData;
    }
}