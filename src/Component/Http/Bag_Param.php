<?php
namespace Slime\Component\Http;

class Bag_Param extends Bag_Base
{
    /**
     * @param null|string|array $m_n_sKey_aKeys
     *
     * @return null|array|string
     */
    public function find($m_n_sKey_aKeys)
    {
        if ($m_n_sKey_aKeys === null) {
            return $this->aData;
        } elseif (is_array($m_n_sKey_aKeys)) {
            return array_intersect_key($this->aData, array_flip($m_n_sKey_aKeys));
        } else {
            return $this->aData[$m_n_sKey_aKeys];
        }
    }
}
