<?php
namespace Slime\Component\RDS\Model;

use Psr\Log\LoggerInterface;
use Slime\Component\RDS\CURD;

class Model
{
    public $CURD;
    public $sTable;
    public $sPKName;
    public $sFKName;
    public $aRelConf;
    public $Factory;
    public $Logger;

    public function __construct($sModelName, CURD $CURD, $aConfig, Factory $Factory, LoggerInterface $Logger)
    {
        $this->CURD     = $CURD;
        $this->sTable   = isset($aConfig['table']) ? $aConfig['table'] : strtolower($sModelName);
        $this->sPKName  = isset($aConfig['pk']) ? $aConfig['pk'] : 'id';
        $this->sFKName  = isset($aConfig['fk']) ? $aConfig['fk'] : $this->sTable . '_id';
        $this->aRelConf = isset($aConfig['relation']) ? $aConfig['relation'] : array();
        $this->Factory  = $Factory;
        $this->Logger   = $Logger;
    }

    public function addUpdate($aKVMap, $aUpdateKey)
    {
        return $this->CURD->insertUpdateSmarty($this->sTable, $aKVMap, $aUpdateKey);
    }

    public function createItem(array $aData = array())
    {
        return new Item($aData, $this);
    }

    public function add($aKVMap)
    {
        return $this->createItem($aKVMap)->add();
    }

    public function delete($mPKOrWhere)
    {
        $Model = $this->find($mPKOrWhere);
        if ($Model === null) {
            return false;
        }
        return $Model->delete();
    }

    public function update($mPKOrWhere, $aKVMap)
    {
        $Model = $this->find($mPKOrWhere);
        if ($Model === null) {
            return false;
        }
        $Model->set($aKVMap);
        return $Model->update();
    }

    /**
     * @param mixed $mPKOrWhere
     *
     * @return Item|null
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
        return empty($aItem) ? null : new Item($aItem, $this);
    }

    /**
     * @param array  $aWhere
     * @param string $sOrderBy
     * @param int    $iLimit
     * @param int    $iOffset
     * @param string $sAttr
     *
     * @return Group|null|Item[]
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
        if (empty($aaData)) {
            return null;
        }

        $Group = new Group($this, $this->Logger);
        foreach ($aaData as $aRow) {
            $Group[$aRow[$this->sPKName]] = new Item($aRow, $this, $Group);
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
}
