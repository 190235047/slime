<?php
namespace SlimeFramework\Component\RDS;

class Model_Item
{
    private $aData;
    private $aOldData;
    private $aRelation;

    /** @var Engine */
    public $Engine;

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
            $this->aRelation[$sModelName] = $this->Engine->relation($sModelName, $this);
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
        ;
    }

    public function __toString()
    {
        return json_encode($this->aData);
    }
}