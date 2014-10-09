<?php
namespace Slime\Component\RDBMS\ORM;

use Slime\Component\RDBMS\DAL\Val;

/**
 * Class Item
 *
 * @package Slime\Component\RDBMS\ORM
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

    /**
     * @param array        $aData
     * @param Model        $Model
     * @param Group | null $Group
     */
    public function __construct(array $aData, $Model, $Group = null)
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
     * @param array $aKVMap
     */
    public function set(array $aKVMap)
    {
        $this->_set($aKVMap);
    }

    protected function _set($mK, $mV = null)
    {
        if (is_array($mK)) {
            foreach ($mK as $sKey => $sValue) {
                if (array_key_exists($sKey, $this->aData)) {
                    if ($this->aData[$sKey] !== $sValue) {
                        $this->aOldData[$sKey] = $this->aData[$sKey];
                        $this->aData[$sKey]    = $sValue;
                    }
                } else {
                    $this->aOldData[$sKey] = '';
                    $this->aData[$sKey]    = $sValue;
                }
            }
        } else {
            if (array_key_exists($mK, $this->aData)) {
                if ($this->aData[$mK] !== $mV) {
                    $this->aOldData[$mK] = $this->aData[$mK];
                    $this->aData[$mK]    = $mV;
                }
            } else {
                $this->aOldData[$mK] = '';
                $this->aData[$mK]    = $mV;
            }
        }
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
     * @param string                                        $sModelName
     * @param array | \Slime\Component\RDBMS\DAL\SQL_SELECT $aWhere_SQLSEL
     * @param string                                        $sOrderBy
     * @param int                                           $iLimit
     * @param int                                           $iOffset
     *
     * @return $this|$this[]
     * @throws \OutOfRangeException
     */
    public function relation(
        $sModelName,
        $aWhere_SQLSEL = array(),
        $sOrderBy = null,
        $iLimit = null,
        $iOffset = null
    ) {
        $mResult = null;

        if (!isset($this->Model->aRelationConfig[$sModelName])) {
            throw new \OutOfRangeException("[MODEL] : Can not find relation for [$sModelName]");
        }

        $sMethod = strtolower($this->Model->aRelationConfig[$sModelName]);
        if ($sMethod === 'hasone' || $sMethod === 'belongsto') {
            $mResult = $this->Group === null ?
                $this->$sMethod($sModelName) :
                $this->Group->relation($sModelName, $this);
        } else {
            $mResult = $this->$sMethod($sModelName, $aWhere_SQLSEL, $sOrderBy, $iLimit, $iOffset);
        }

        if ($mResult === null && $this->Model->Factory->isCompatibleMode()) {
            $mResult = new CItem();
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
            throw new \OutOfRangeException("[MODEL] : Can not find relation for [$sModelName]");
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
        if ($this->Model->add($this->aData, true, $iLastID) === false) {
            return false;
        } else {
            $this->aData[$this->Model->sPKName] = $iLastID;
            return true;
        }
    }

    /**
     * @return bool | null | int [null:无需更新]
     */
    public function update()
    {
        $aUpdate = array_intersect_key($this->aData, $this->aOldData);
        if (empty($aUpdate)) {
            return null;
        }
        $bRS = $this->Model->update(array($this->Model->sPKName => $this->aData[$this->Model->sPKName]), $aUpdate);
        if ($bRS) {
            $this->aOldData = array();
        }
        return $bRS;
    }


    /**
     * @return bool
     */
    public function delete()
    {
        return $this->Model->delete(array($this->Model->sPKName => $this->aData[$this->Model->sPKName]));
    }

    /**
     * @param string $sModelName
     *
     * @return Item|null
     */
    public function hasOne($sModelName)
    {
        $Model = $this->Model->Factory->get($sModelName);
        return $Model->find(array($this->Model->sFKName => $this->aData[$this->Model->sPKName]));
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
     * @param string                                        $sModel
     * @param array | \Slime\Component\RDBMS\DAL\SQL_SELECT $aWhere_SQLSEL
     * @param string                                        $sOrderBy
     * @param int                                           $iLimit
     * @param int                                           $iOffset
     *
     * @return Group|Item[]
     */
    public function hasMany(
        $sModel,
        $aWhere_SQLSEL = array(),
        $sOrderBy = null,
        $iLimit = null,
        $iOffset = null
    ) {
        $Model = $this->Model->Factory->get($sModel);

        return $Model->findMulti(
            is_array($aWhere_SQLSEL) ?
                (empty($aWhere) ?
                    array($this->Model->sFKName => $this->aData[$Model->sPKName]) :
                    array_merge(array($this->Model->sFKName => $this->aData[$Model->sPKName]), $aWhere)
                ) :
                $aWhere_SQLSEL,
            $sOrderBy,
            $iLimit,
            $iOffset
        );
    }

    /**
     * @param string                                        $sModel
     * @param array | \Slime\Component\RDBMS\DAL\SQL_SELECT $aWhere_SQLSEL
     *
     * @return int
     */
    public function hasManyCount($sModel, $aWhere_SQLSEL = array())
    {
        $Model = $this->Model->Factory->get($sModel);

        return $Model->findCount(
            is_array($aWhere_SQLSEL) ?
                (empty($aWhere) ?
                    array($this->Model->sFKName => $this->aData[$Model->sPKName]) :
                    array_merge(array($this->Model->sFKName => $this->aData[$Model->sPKName]), $aWhere)
                ) :
                $aWhere_SQLSEL
        );
    }

    /**
     * @param string                                        $sModelTarget
     * @param array | \Slime\Component\RDBMS\DAL\SQL_SELECT $aWhere_SQLSEL
     * @param string                                        $nsOrderBy
     * @param int                                           $niLimit
     * @param int                                           $niOffset
     *
     * @return null|Group|Item[]
     */
    public function hasManyThrough(
        $sModelTarget,
        $aWhere_SQLSEL = array(),
        $nsOrderBy = null,
        $niLimit = null,
        $niOffset = null
    ) {
        $MTarget   = $this->Model->Factory->get($sModelTarget);
        $MOrg      = $this->Model;
        $sRelTName = self::getTableNameFromManyThrough($MTarget, $MOrg);

        if (is_array($aWhere_SQLSEL)) {
            $SQL = $MOrg->SQL_R()
                ->join(
                    $sRelTName,
                    array(
                        "{$MTarget->sTable}.{$MTarget->sPKName}" => Val::Name("$sRelTName.{$MTarget->sFKName}")
                    )
                )
                ->fields("{$MTarget->sTable}.*")
                ->where($aWhere_SQLSEL);
            if ($nsOrderBy !== null) {
                $SQL->orderBy($nsOrderBy);
            }
            if ($niLimit !== null) {
                $SQL->limit($niLimit);
            }
            if ($niOffset !== null) {
                $SQL->offset($niLimit);
            }

            return $MTarget->findMulti($SQL);
        } else {
            return $MTarget->findMulti($aWhere_SQLSEL);
        }
    }

    /**
     * @param string                                        $sModelTarget
     * @param array | \Slime\Component\RDBMS\DAL\SQL_SELECT $aWhere_SQLSEL
     *
     * @return bool | int
     */
    public function hasManyThroughCount($sModelTarget, $aWhere_SQLSEL = array())
    {
        $MTarget   = $this->Model->Factory->get($sModelTarget);
        $MOrg      = $this->Model;
        $sRelTName = self::getTableNameFromManyThrough($MTarget, $MOrg);

        if (is_array($aWhere_SQLSEL)) {
            $SQL = $MOrg->SQL_R()
                ->join(
                    $sRelTName,
                    array(
                        "{$MTarget->sTable}.{$MTarget->sPKName}" => Val::Name("$sRelTName.{$MTarget->sFKName}")
                    )
                )
                ->fields("{$MTarget->sTable}.*")
                ->where($aWhere_SQLSEL);

            return $MTarget->findCount($SQL);
        } else {
            return $MTarget->findCount($aWhere_SQLSEL);
        }
    }

    /**
     * @param Model $M1
     * @param Model $M2
     *
     * @return string
     */
    public static function getTableNameFromManyThrough($M1, $M2)
    {
        //@todo find in config
        $sRelatedTableName = 'rel__' . (strcmp($M1->sTable, $M2->sTable) > 0 ?
                $M2->sTable . '__' . $M1->sTable :
                $M1->sTable . '__' . $M2->sTable);
        return $sRelatedTableName;
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