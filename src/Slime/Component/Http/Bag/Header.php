<?php
namespace Slime\Component\Http;

/**
 * Class Bag_Header
 *
 * @package Slime\Component\Http
 * @author  smallslime@gmail.com
 */
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