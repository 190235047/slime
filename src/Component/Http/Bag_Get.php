<?php
namespace Slime\Component\Http;

/**
 * Class Bag_Get
 *
 * @package Slime\Component\Http
 * @author  smallslime@gmail.com
 */
class Bag_Get extends Bag_Base
{
    public function buildQuery()
    {
        return http_build_query($this->aData);
    }
}