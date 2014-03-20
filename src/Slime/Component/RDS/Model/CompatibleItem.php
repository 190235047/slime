<?php
namespace Slime\Component\RDS\Model;

/**
 * Class CompatibleItem
 *
 * @package Slime\Component\RDS\Model
 * @author  smallslime@gmail.com
 */
class CompatibleItem
{
    public function __call($sMethod, $aArg)
    {
        return $this;
    }

    public function __get($sName)
    {
        return null;
    }

    public function __toString()
    {
        return '';
    }
}