<?php
namespace Slime\Component\RDS;

use Psr\Log\LoggerInterface;
use Slime\Component\RDS\Model_Pool;

class Model_Model
{
    public function __construct($sModelName, CURD $CURD, $aConfig, Model_Pool $Pool, LoggerInterface $Log)
    {
        $this->CURD     = $CURD;
        $this->sTable   = isset($aConfig['table']) ? $aConfig['table'] : strtolower($sModelName);
        $this->sPKName  = isset($aConfig['pk']) ? $aConfig['pk'] : 'id';
        $this->sFKName  = isset($aConfig['fk']) ? $aConfig['fk'] : $this->sTable . '_id';
        $this->aRelConf = isset($aConfig['relation']) ? $aConfig['relation'] : array();
        $this->Pool     = $Pool;
        $this->Log      = $Log;
    }

    public function createItem(array $aData = array())
    {
        return new Model_Item($aData, $this);
    }

    public function add($aKVMap)
    {
        return $this->CURD->insertSmarty($this->sTable, $aKVMap);
    }

    public function addUpdate($aKVMap, $aUpdateKey)
    {
        return $this->CURD->insertUpdateSmarty($this->sTable, $aKVMap, $aUpdateKey);
    }

    public function delete($mPK)
    {
        return $this->CURD->deleteSmarty($this->sTable, array($this->sPKName => $mPK));
    }

    public function update($mPK, $aKVMap)
    {
        return $this->CURD->updateSmarty($this->sTable, $aKVMap, array($this->sPKName => $mPK));
    }

    /**
     * @param mixed $mPKOrWhere
     *
     * @return Model_Item|null
     */
    public function find($mPKOrWhere)
    {
        $aWhere = is_array($mPKOrWhere) ? $mPKOrWhere : array($this->sPKName => $mPKOrWhere);

        $aItem = $this->CURD->querySmarty(
            $this->sTable,
            $aWhere,
            '',
            '',
            true
        );
        return empty($aItem) ? null : new Model_Item($aItem, $this);
    }

    /**
     * @param array  $aWhere
     * @param string $sOrderBy
     * @param int    $iLimit
     * @param int    $iOffset
     * @param string $sAttr
     *
     * @return Model_Group|null
     */
    public function findMulti($aWhere = array(), $sOrderBy = null, $iLimit = null, $iOffset = null, $sAttr = '')
    {
        $sOrderBy !== null && $sAttr .= " ORDER BY $sOrderBy";
        $iLimit !== null && $sAttr .= " LIMIT $iLimit";
        $iOffset !== null && $sAttr .= " OFFSET $iOffset";

        $aaData = $this->CURD->querySmarty(
            $this->sTable,
            $aWhere,
            $sAttr
        );

        $Group = new Model_Group($this, $this->Log);
        if (!empty($aaData)) {
            foreach ($aaData as $aRow) {
                $Group[$aRow[$this->sPKName]] = new Model_Item($aRow, $this, $Group);
            }
        }
        return $Group;
    }

    /**
     * @param array $aWhere
     *
     * @return int
     */
    public function findCount($aWhere = array())
    {
        return $this->CURD->queryCount($this->sTable, $aWhere);
    }

    /**
     * @param array  $aWhere
     * @param string $sAttr
     * @param string $sSelect
     * @param bool   $bOnlyOne
     * @param        $iFetchStyle
     * @param null   $mFetchArgs
     *
     * @return array|bool|mixed
     */
    public function findCustom(
        $aWhere = array(),
        $sAttr = '',
        $sSelect = '',
        $bOnlyOne = false,
        $iFetchStyle = \PDO::FETCH_ASSOC,
        $mFetchArgs = null
    ) {
        return $this->CURD->querySmarty($this->sTable, $aWhere, $sAttr, $sSelect, $bOnlyOne, $iFetchStyle, $mFetchArgs);
    }

    /**
     * @param string     $sModelName
     * @param Model_Item $ModelItem
     *
     * @return Model_Item|Model_Item[]|null
     */
    public function relation($sModelName, Model_Item $ModelItem)
    {
        if (!isset($this->aRelConf[$sModelName])) {
            $this->Log->error('Relation model {model} is not exist', array('model' => $sModelName));
        }
        $sMethod = $this->aRelConf[$sModelName];
        return $this->$sMethod($sModelName, $ModelItem);
    }

    /**
     * @param string     $sModelName
     * @param Model_Item $ModelItem
     *
     * @return Model_Item|null
     */
    public function hasOne($sModelName, $ModelItem)
    {
        $Model = $this->Pool->get($sModelName);
        return $Model->find(array($this->sFKName => $ModelItem[$this->sPKName]));
    }

    /**
     * @param string     $sModelName
     * @param Model_Item $ModelItem
     *
     * @return Model_Item|null
     */
    public function belongsTo($sModelName, $ModelItem)
    {
        $Model = $this->Pool->get($sModelName);
        return $Model->find(array($Model->sPKName => $ModelItem[$Model->sFKName]));
    }

    /**
     * @param string     $sModel
     * @param Model_Item $ModelItem
     *
     * @return Model_Group
     */
    public function hasMany($sModel, $ModelItem)
    {
        $Model = $this->Pool->get($sModel);
        return $Model->findMulti(array($this->sFKName => $ModelItem->{$Model->sPKName}));
    }

    /**
     * @param string      $sModelTarget
     * @param Model_Item  $ModelItem
     * @param string|null $sModelRelated
     *
     * @return null|Model_Group
     */
    public function hasManyThough($sModelTarget, Model_Item $ModelItem, $sModelRelated = null)
    {
        $ModelTarget = $this->Pool->get($sModelTarget);
        $ModelOrg    = $ModelItem->Model;
        if ($sModelRelated === null) {
            $sRelatedTableName = strcmp($ModelOrg->sTable, $ModelTarget->sTable) > 0 ?
                $ModelTarget->sTable . '_' . $ModelOrg->sTable :
                $ModelOrg->sTable . '_' . $ModelTarget->sTable;
            $CURD              = $ModelOrg->CURD;
        } else {
            $ModelRelated      = $this->Pool->get($sModelRelated);
            $CURD              = $ModelRelated->CURD;
            $sRelatedTableName = $ModelRelated->sTable;
        }

        $aIDS = $CURD->querySmarty(
            $sRelatedTableName,
            array($ModelOrg->sFKName => $ModelItem->{$ModelOrg->sFKName}),
            '',
            $ModelTarget->sFKName,
            false,
            \PDO::FETCH_COLUMN
        );

        $Group = null;
        if (!empty($aIDS)) {
            $Group = $ModelTarget->findMulti($aIDS);
        }

        return $Group;
    }
}
