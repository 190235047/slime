<?php
namespace Slime\Component\RDS\Model;

/**
 * Class Item
 *
 * @package Slime\Component\RDS
 * @author  smallslime@gmail.com
 *
 * @property-read array $aData
 * @property-read array $aOldData
 */
class Item implements \ArrayAccess
{
    /** @var Model */
    public $Model;

    /** @var Group|null */
    public $Group;

    /** @var array */
    public $aData;

    /** @var array */
    public $aOldData = array();

    public function __construct(array $aData, Model $Model, $Group = null)
    {
        $this->aData = $aData;
        $this->Model = $Model;
        $this->Group = $Group;
    }

    public function __get($sKey)
    {
        return isset($this->aData[$sKey]) ? $this->aData[$sKey] : null;
    }

    public function __set($sKey, $mValue)
    {
        $this->_set($sKey, $mValue);
    }

    /**
     * @param string|array $mKeyOrKVMap
     * @param null         $mValue
     *
     * @return $this
     */
    public function set($mKeyOrKVMap, $mValue = null)
    {
        if (!is_array($mKeyOrKVMap)) {
            $this->_set($mKeyOrKVMap, $mValue);
        } else {
            foreach ($mKeyOrKVMap as $sKey => $mValue) {
                $this->_set($sKey, $mValue);
            }
        }
        return $this;
    }

    private function _set($sKey, $mValue)
    {
        if (isset($this->aData[$sKey]) && $this->aData[$sKey] == $mValue) {
            return;
        }
        $this->aOldData[$sKey] = isset($this->aData[$sKey]) ? $this->aData[$sKey] : null;
        $this->aData[$sKey]    = $mValue;
    }

    /**
     * @param string $sModelName
     * @param array  $mValue
     *
     * @return $this|$this[]
     */
    public function __call($sModelName, $mValue = array())
    {
        if (substr($sModelName, 0, 5) === 'count') {
            $sModelName = substr($sModelName, 5);
            $sMethod    = 'relationCount';
        } else {
            $sMethod = 'relation';
        }
        if (empty($mValue)) {
            return $this->$sMethod($sModelName);
        } else {
            array_unshift($mValue, $sModelName);
            return call_user_func_array(array($this, $sMethod), $mValue);
        }
    }

    /**
     * @param string $sModelName
     * @param array  $aWhere
     * @param string $sOrderBy
     * @param int    $iLimit
     * @param int    $iOffset
     * @param bool   $bJoin
     *
     * @return $this|$this[]
     * @throws \OutOfRangeException
     */
    public function relation(
        $sModelName,
        array $aWhere = null,
        $sOrderBy = null,
        $iLimit = null,
        $iOffset = null,
        $bJoin = false
    ) {
        $mResult = null;

        if (!isset($this->Model->aRelationConfig[$sModelName])) {
            throw new \OutOfRangeException("Can not find relation for [$sModelName]");
        }

        $sMethod = strtolower($this->Model->aRelationConfig[$sModelName]);
        if ($sMethod === 'hasone' || $sMethod === 'belongsto') {
            $mResult = $this->Group === null ?
                $this->$sMethod($sModelName) :
                $this->Group->relation($sModelName, $this);
        } else {
            $mResult = $this->$sMethod($sModelName, $aWhere, $sOrderBy, $iLimit, $iOffset, $bJoin);
        }

        if ($mResult === null && $this->Model->Factory->bCompatibleMode === true) {
            $mResult = new CompatibleItem();
        }

        return $mResult;
    }

    /**
     * @param string     $sModelName
     * @param array|null $aWhere
     *
     * @return int
     * @throws \OutOfRangeException
     */
    public function relationCount($sModelName, array $aWhere = null)
    {
        if (!isset($this->Model->aRelationConfig[$sModelName])) {
            throw new \OutOfRangeException("Can not find relation for [$sModelName]");
        }

        $sMethod = strtolower($this->Model->aRelationConfig[$sModelName]);
        if ($sMethod === 'hasone' || $sMethod === 'belongsto') {
            return null;
        }

        $sMethod .= 'Count';
        return $this->$sMethod($sModelName, $aWhere);

    }

    /**
     * @return bool
     */
    public function add()
    {
        $M   = $this->Model;
        $iID = $M->CURD->insertSmarty($M->sTable, $this->aData);
        if ($iID === null) {
            $bRS = false;
        } else {
            $this->aData[$M->sPKName] = $iID;
            $bRS                      = true;
        }
        return $bRS;
    }

    /**
     * @return bool
     */
    public function delete()
    {
        $M = $this->Model;
        return $M->CURD->deleteSmarty($M->sTable, array($M->sPKName => $this->aData[$M->sPKName]));
    }

    /**
     * @return int|bool [int:非pdo错误; true:更新成功; false:更新失败]
     */
    public function update()
    {
        $aUpdate = array_intersect_key($this->aData, $this->aOldData);
        if (empty($aUpdate)) {
            return 99;
        }
        $M   = $this->Model;
        $bRS = $M->CURD->updateSmarty(
            $M->sTable,
            $aUpdate,
            array($M->sPKName => $this->aData[$M->sPKName])
        );
        if ($bRS) {
            $this->aOldData = array();
        }
        return $bRS;
    }

    /**
     * @param string $sModelName
     *
     * @return Item|null
     */
    public function hasOne($sModelName)
    {
        $M     = $this->Model;
        $Model = $M->Factory->get($sModelName);
        return $Model->find(array($M->sFKName => $this->aData[$M->sPKName]));
    }

    /**
     * @param string $sModelName
     *
     * @return Item|null
     */
    public function belongsTo($sModelName)
    {
        $Model = $this->Model->Factory->get($sModelName);
        return $Model->find(array($Model->sPKName => $this->aData[$Model->sFKName]));
    }

    /**
     * @param string $sModel
     * @param array  $aWhere
     * @param string $sOrderBy
     * @param int    $iLimit
     * @param int    $iOffset
     *
     * @return Group|Item[]
     */
    public function hasMany(
        $sModel,
        array $aWhere = null,
        $sOrderBy = null,
        $iLimit = null,
        $iOffset = null
    ) {
        $M     = $this->Model;
        $Model = $M->Factory->get($sModel);

        return $Model->findMulti(
            (empty($aWhere) ?
                array($M->sFKName => $this->aData[$Model->sPKName]) :
                array_merge(array($M->sFKName => $this->aData[$Model->sPKName]), $aWhere)
            ),
            $sOrderBy,
            $iLimit,
            $iOffset
        );
    }

    /**
     * @param string $sModel
     * @param array  $aWhere
     *
     * @return int
     */
    public function hasManyCount($sModel, array $aWhere = null)
    {
        $M     = $this->Model;
        $Model = $M->Factory->get($sModel);

        return $Model->findCount(
            (empty($aWhere) ?
                array($M->sFKName => $this->aData[$Model->sPKName]) :
                array_merge(array($M->sFKName => $this->aData[$Model->sPKName]), $aWhere)
            )
        );
    }

    /**
     * @param string $sModelTarget
     * @param array  $aWhere
     * @param string $sOrderBy
     * @param int    $iLimit
     * @param int    $iOffset
     *
     * @return null|Group|Item[]
     */
    public function hasManyThrough(
        $sModelTarget,
        array $aWhere = null,
        $sOrderBy = null,
        $iLimit = null,
        $iOffset = null
    ) {
        $ModelTarget = $this->Model->Factory->get($sModelTarget);
        $ModelOrg    = $this->Model;
        //@todo $sRelatedTableName declare in config
        $sRelatedTableName = 'rel__' . (strcmp($ModelOrg->sTable, $ModelTarget->sTable) > 0 ?
                $ModelTarget->sTable . '__' . $ModelOrg->sTable :
                $ModelOrg->sTable . '__' . $ModelTarget->sTable);

        $sMTPKName    = $ModelTarget->sPKName;
        $sMTFKName    = $ModelTarget->sFKName;
        $sMTTableName = $ModelTarget->sTable;
        $sTable       = "$sMTTableName JOIN $sRelatedTableName ON $sMTTableName.$sMTPKName = $sRelatedTableName.$sMTFKName";
        $sSelect      = "$sMTTableName.*";
        $aArrTable    = array($ModelTarget->sTable, $sRelatedTableName);

        $aNewWhere = array("$sRelatedTableName.{$ModelOrg->sFKName}" => $this->aData[$ModelOrg->sPKName]);
        if (!empty($aWhere)) {
            $aNewWhere = array($aNewWhere, self::preReplace($aWhere, $aArrTable));
        }
        if (!empty($sOrderBy)) {
            $sOrderBy = self::preReplace($sOrderBy, $aArrTable);
        }

        return $ModelTarget->findMulti($aNewWhere, $sOrderBy, $iLimit, $iOffset, $sTable, $sSelect);
    }

    public function hasManyThroughCount($sModelTarget, array $aWhere = null)
    {
        $ModelTarget = $this->Model->Factory->get($sModelTarget);
        $ModelOrg    = $this->Model;
        //@todo $sRelatedTableName declare in config
        $sRelatedTableName = 'rel__' . (strcmp($ModelOrg->sTable, $ModelTarget->sTable) > 0 ?
                $ModelTarget->sTable . '__' . $ModelOrg->sTable :
                $ModelOrg->sTable . '__' . $ModelTarget->sTable);

        $sMTPKName    = $ModelTarget->sPKName;
        $sMTFKName    = $ModelTarget->sFKName;
        $sMTTableName = $ModelTarget->sTable;
        $sTable       = "$sMTTableName JOIN $sRelatedTableName ON $sMTTableName.$sMTPKName = $sRelatedTableName.$sMTFKName";
        $aArrTable    = array($ModelTarget->sTable, $sRelatedTableName);
        $aNewWhere    = array("$sRelatedTableName.{$ModelOrg->sFKName}" => $this->aData[$ModelOrg->sPKName]);
        if (!empty($aWhere)) {
            $aNewWhere = array($aNewWhere, self::preReplace($aWhere, $aArrTable));
        }

        return $ModelTarget->findCount($aNewWhere, $sTable);
    }

    /**
     * @param array|string $mMix
     * @param array        $aArrTable
     *
     * @return array|string
     */
    public static function preReplace($mMix, array $aArrTable)
    {
        if (!is_array($mMix)) {
            return is_string($mMix) ? str_replace(array('!', '@'), $aArrTable, $mMix) : $mMix;
        }

        $aWhereNew = array();
        foreach ($mMix as $mK => $mV) {
            $sFixKey             = is_string($mK) ? self::preReplace($mK, $aArrTable) : $mK;
            $aWhereNew[$sFixKey] = self::preReplace($mV, $aArrTable);
        }

        return $aWhereNew;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->aData;
    }

    public function __toString()
    {
        return var_export($this->aData, true);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)
     * Whether a offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset An offset to check for.
     *
     * @return boolean true on success or false on failure.
     *       The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->aData);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return isset($this->aData[$offset]) ? $this->aData[$offset] : null;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value  The value to set.
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->_set($offset, $value);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset The offset to unset.
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->aData[$offset]);
    }
}