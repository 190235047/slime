<?php
namespace SlimeFramework\Component\Http;

class Bag_Cookie extends \ArrayObject
{
    public function __toString()
    {
        $aResult = '';
        $aArr = $this->getArrayCopy();
        foreach ($aArr as $sK => $mV) {
            $aResult[] = "$sK=$mV";
        }
        return implode('; ' , $aResult);
    }
}