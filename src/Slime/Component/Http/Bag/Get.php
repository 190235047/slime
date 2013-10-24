<?php
namespace Slime\Component\Http;

class Bag_Get extends Bag_Bag
{
    public function buildQuery()
    {
        return http_build_query($this->aData);
    }
}