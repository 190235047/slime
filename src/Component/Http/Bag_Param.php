<?php
namespace Slime\Component\Http;

class Bag_Param extends Bag_Base
{
    /**
     * @param string|array $nsaKeyOrKeys
     *
     * @return null|array|string
     */
    public function find($nsaKeyOrKeys)
    {
        if ($nsaKeyOrKeys === null) {
            return $this->XssStatus && $this->XssStatus->value ? $this->XssStatus->XSS->clean($this->aData) : $this->aData;
        } elseif (is_array($nsaKeyOrKeys)) {
            $aResult = array_intersect_key($this->aData, array_flip($nsaKeyOrKeys));
            return $this->XssStatus && $this->XssStatus->value ? $this->XssStatus->XSS->clean($aResult) : $aResult;
        } else {
            return isset($this->aData[$nsaKeyOrKeys]) ?
                ($this->XssStatus && $this->XssStatus->value ?
                    $this->XssStatus->XSS->clean($this->aData[$nsaKeyOrKeys]) :
                    $this->aData[$nsaKeyOrKeys]
                ) : null;
        }
    }
}
