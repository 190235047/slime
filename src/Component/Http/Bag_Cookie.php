<?php
namespace Slime\Component\Http;

/**
 * Class Bag_Cookie
 *
 * @package Slime\Component\Http
 * @author  smallslime@gmail.com
 */
class Bag_Cookie extends Bag_Base
{
    public function __toString()
    {
        $aResult = array();
        $aArr    = $this->toArray();
        foreach ($aArr as $sK => $mV) {
            $aResult[] = "$sK=$mV";
        }
        return implode('; ', $aResult);
    }
}