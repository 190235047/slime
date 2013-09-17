<?php
namespace SlimeFramework\Component\RDS;

use SlimeFramework\Component\Log\Logger;
use SlimeFramework\Component\RDS\Model_Pool;

class Engine
{
    private $CURD;

    public function __construct(CURD $CURD, $aConfig, Model_Pool $Pool, Logger $Log)
    {
        $this->CURD = $CURD;

        # @todo define default
        $this->sTable    = $aConfig['table'];
        $this->sPK       = $aConfig['pk'];
        $this->sFK       = $aConfig['fk'];
        $this->aRelation = $aConfig['rel'];
        $this->Pool      = $Pool;

        # @todo loop relation , do something in destruct
        $this->Log = $Log;
    }

    public function add($aKVMap)
    {
        $this->CURD->insertSmarty($this->sTable, $aKVMap);
    }

    public function delete($mPK)
    {
        $this->CURD->deleteSmarty($this->sTable, array($this->sPK => $mPK));
    }

    public function update($mPK, $aKVMap)
    {
        $this->CURD->updateSmarty($this->sTable, $aKVMap, array($this->sPK => $mPK));
    }

    /**
     * @param $mPKOrWhere
     * @return Model_Item
     */
    public function find($mPKOrWhere)
    {
        $aWhere = is_array($mPKOrWhere) ? $mPKOrWhere : array($this->sPK => $mPKOrWhere);

        /** @var Model_Item $Item */
        $Item = $this->CURD->querySmarty(
            $this->sTable,
            $aWhere,
            '',
            '',
            true,
            \PDO::FETCH_CLASS,
            array('\\SlimeFramework\Component\\RDS\\Model_Item')
        );
        if ($Item instanceof Model_Item) {
            $Item->Engine = $this;
        }
        return $Item;
    }

    public function findMulti($aWhere = array(), $sOrderBy = null, $iLimit = null, $iOffset = null)
    {
        $sAttr = '';
        $sOrderBy !== null && $sAttr .= "ORDER BY $sOrderBy";
        $iLimit !== null && $sAttr .= "LIMIT $iLimit";
        $iOffset !== null && $sAttr .= "OFFSET $iOffset";

        /** @var Model_Item[] $aItem */
        $aItem = $this->CURD->querySmarty(
            $this->sTable,
            $aWhere,
            $sAttr,
            '',
            false,
            \PDO::FETCH_CLASS,
            array('\\SlimeFramework\Component\\RDS\\Model_Item')
        );

        $Group = new Model_Group();
        foreach ($aItem as $Item) {
            $Group[$Item->{$this->sPK}] = $Item;
        }
        $Group->Engine = $this;

        return $Group;
    }

    public function findCount()
    {
        ;
    }

    public function create()
    {
        return new Model_Item($this, array());
    }

    /**
     * @param $sModel
     * @param Model_Item $ModelItem
     * @return Model_Item|Model_Item[]|null
     */
    public function relation($sModel, Model_Item $ModelItem)
    {
        if (!isset($this->aRelation[$sModel])) {
            $this->Log->error('Relation model {model} is not exist', array('model' => $sModel));
        }
        $sMethod = $this->aRelation[$sModel];
        return $this->$sMethod($sModel, $ModelItem);
    }

    /**
     * @param $sModel
     * @param Model_Item $ModelItem
     * @return Model_Item|null
     */
    public function hasOne($sModel, $ModelItem)
    {
        $Engine = $this->Pool->get($sModel);
        return $Engine->find(array($this->sFK => $ModelItem->{$this->sPK}));
    }

    /**
     * @param $sModel
     * @param Model_Item $ModelItem
     * @return Model_Item|mixed
     */
    public function belongsTo($sModel, $ModelItem)
    {
        $Engine = $this->Pool->get($sModel);
        return $Engine->find(array($Engine->sPK => $ModelItem->{$Engine->sPK}));
    }

    /**
     * @param $sModel
     * @param Model_Item $ModelItem
     * @return Model_Item|mixed
     */
    public function hasMany($sModel, $ModelItem = null)
    {
        $Engine = $this->Pool->get($sModel);
        return $Engine->findMulti(array($this->sFK => $ModelItem->{$Engine->sPK}));
    }

    public function hasManyThough($sModel, $mPKOrWhere = null)
    {
        ;
    }
}
