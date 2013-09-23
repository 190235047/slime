<?php
namespace SlimeFramework\Component\RDS;

use Psr\Log\LoggerInterface;
use SlimeFramework\Component\RDS\Model_Pool;

class Model_Model
{
    private $CURD;

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

    public function delete($mPK)
    {
        $this->CURD->deleteSmarty($this->sTable, array($this->sPKName => $mPK));
    }

    public function update($mPK, $aKVMap)
    {
        return $this->CURD->updateSmarty($this->sTable, $aKVMap, array($this->sPKName => $mPK));
    }

    /**
     * @param $mPKOrWhere
     * @return Model_Item
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
        return new Model_Item($aItem, $this);
    }

    public function findMulti($aWhere = array(), $sOrderBy = null, $iLimit = null, $iOffset = null, $sAttr = '')
    {
        $sOrderBy !== null && $sAttr .= " ORDER BY $sOrderBy";
        $iLimit !== null && $sAttr .= " LIMIT $iLimit";
        $iOffset !== null && $sAttr .= " OFFSET $iOffset";

        $aaData = $this->CURD->querySmarty(
            $this->sTable,
            $aWhere,
            $sAttr,
            '',
            false
        );

        $Group = new Model_Group($this, $this->Log);
        if (!empty($aaData)) {
            foreach ($aaData as $aRow) {
                $Group[$aRow[$this->sPKName]] = new Model_Item($aRow, $this);
            }
        }
        return $Group;
    }

    public function findCount()
    {
    }

    /**
     * @param string $sModelName
     * @param Model_Item $ModelItem
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
     * @param string $sModelName
     * @param Model_Item $ModelItem
     * @return Model_Item|null
     */
    public function hasOne($sModelName, $ModelItem)
    {
        $Model = $this->Pool->get($sModelName);
        return $Model->find(array($this->sFKName => $ModelItem->{$this->sPKName}));
    }

    /**
     * @param string $sModelName
     * @param Model_Item $ModelItem
     * @return Model_Item|null
     */
    public function belongsTo($sModelName, $ModelItem)
    {
        $Model = $this->Pool->get($sModelName);
        return $Model->find(array($Model->sPKName => $ModelItem->{$Model->sPKName}));
    }

    /**
     * @param $sModel
     * @param Model_Item $ModelItem
     * @return Model_Group
     */
    public function hasMany($sModel, $ModelItem = null)
    {
        $Model = $this->Pool->get($sModel);
        return $Model->findMulti(array($this->sFKName => $ModelItem->{$Model->sPKName}));
    }

    public function hasManyThough($sModel, $mPKOrWhere = null)
    {
        ;
    }
}
