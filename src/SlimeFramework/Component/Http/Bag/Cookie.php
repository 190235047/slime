<?php
namespace SlimeFramework\Component\Http;

class Bag_Cookie extends Bag_Bag
{
    public function __toString()
    {
        $aResult = '';
        $aArr = $this->toArray();
        foreach ($aArr as $sK => $mV) {
            $aResult[] = "$sK=$mV";
        }
        return implode('; ' , $aResult);
    }
}