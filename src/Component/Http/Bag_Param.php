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
            $aResult = array_intersect_key($this->aData, array_flip($saKeyOrKeys));
            return $this->bXSSEnable ? Helper_XSS::getInst()->clean($aResult) : $aResult;
        } else {
            return isset($this->aData[$saKeyOrKeys]) ?
                ($this->bXSSEnable ?
                    Helper_XSS::getInst()->clean($this->aData[$saKeyOrKeys]) :
                    $this->aData[$saKeyOrKeys]
                ) : null;
        }
    }
}
