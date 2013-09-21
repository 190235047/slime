<?php
namespace SlimeFramework\Component\RDS;

class Model_Item
{
    private $aData;
    private $aOldData;
    private $aRelation;

    /** @var Model */
    public $Model;

    public function __get($sKey)
    {
        return $this->aData[$sKey];
    }

    /**
     * @param string $sModelName
     * @return $this
     */
    public function rel($sModelName)
    {
        if (!isset($this->aRelation[$sModelName])) {
            $this->aRelation[$sModelName] = $this->Model->relation($sModelName, $this);
        }
        return $this->aRelation[$sModelName];
    }

    public function __set($sKey, $mValue)
    {
        $this->aOldData[$sKey] = $this->aData[$sKey];
        $this->aData[$sKey] = $mValue;
    }

    public function save()
    {
        if (isset($this->aData[$this->Model->sPK])) {
            return $this->Model->update($this->aData[$this->Model->sPK], $this->aData);
        } else {
            $iID = $this->Model->add($this->aData);
            if ($iID===null) {
                return false;
            } else {
                $this->aData[$this->Model->sPK] = $iID;
                return true;
            }
        }
    }

    public function __toString()
    {
        return json_encode($this->aData);
    }
}