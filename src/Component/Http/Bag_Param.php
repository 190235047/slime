<?php
namespace Slime\Component\Http;

class Bag_Param extends Bag_Base
{
    public function find($saKeyOrKeys)
    {
        if (is_array($saKeyOrKeys)) {
            $aResult = array_intersect_key($this->aData, array_flip($saKeyOrKeys));
            return $this->bXSSEnable ? Helper_XSS::getInst()->clean($aResult) : $aResult;
        } else {
            return $this->aData[$saKeyOrKeys];
        }
    }
}
