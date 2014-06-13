<?php
namespace Slime\Component\Http;

class Bag_Param extends Bag_Base
{
    /**
     * @param string|array $saKeyOrKeys
     *
     * @return null|array|string
     */
    public function find($saKeyOrKeys)
    {
        if (is_array($saKeyOrKeys)) {
            return array_intersect_key($this->aData, array_flip($saKeyOrKeys));
        } else {
            return isset($this->aData[$saKeyOrKeys]) ? $this->aData[$saKeyOrKeys] : null;
        }
    }
}
