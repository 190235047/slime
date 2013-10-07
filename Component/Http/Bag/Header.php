<?php
namespace SlimeFramework\Component\Http;

class Bag_Header extends \ArrayObject
{
    public function __toString()
    {
        $sResult = '';
        $aArr = $this->getArrayCopy();
        foreach ($aArr as $sK => $mV) {
            $sResult .= "$sK: " . (string)$mV . "\r\n";
        }
        return $sResult;
    }
}