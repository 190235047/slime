<?php
namespace SlimeFramework\Component\Http;

class Bag_Header extends Bag_Bag
{
    public function __toString()
    {
        $sResult = '';
        $aArr = $this->toArray();
        foreach ($aArr as $sK => $mV) {
            $sV = (string)$mV;
            if ($sV!=='') {
                $sResult .= "$sK: $sV\r\n";
            }
        }
        return $sResult;
    }
}