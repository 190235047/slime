<?php
namespace Slime\Component\RDS\Model;

use Slime\Component\RDS\CURD;

/**
 * Class Model
 *
 * @package Slime\Component\RDS\Model
 * @author  smallslime@gmail.com
 */
class Model
{
    protected $sItemClassNS = 'Slime\\Component\\RDS\\Model';
    protected $sItemClassPartName = 'Item';

    private $sItemClassName;

    public $sModelName;

    public $CURD;
    public $sTable;
    public $sPKName;
    public $sFKName;
    public $aRelationConfig;
    public $Factory;

    /**
     * @param string  $sModelName
     * @param CURD    $CURD
     * @param array   $aConfig
     * @param Factory $Factory
     */
    public function __construct($sModelName, CURD $CURD, $aConfig, Factory $Factory)
    {
        $this->sModelName = $sModelName;
        $this->CURD = $CURD;
        $this->sTable = isset($aConfig['table']) ? $aConfig['table'] : strtolower($sModelName);
        $this->sPKName = isset($aConfig['pk']) ? $aConfig['pk'] : 'id';
        $this->sFKName = isset($aConfig['fk']) ? $aConfig['fk'] : $this->sTable . '_id';
        $this->aRelationConfig = isset($aConfig['relation']) ? $aConfig['relation'] : array();
        $this->Factory = $Factory;
        $this->sItemClassName = $this->sItemClassNS . '\\' . $this->sItemClassPartName;
    }

    /**
     * @param array $aKVMap
     * @param array $aUpdateKey
     *
     * @return bool
     */
    public function addUpdate($aKVMap, $aUpdateKey)
    {
        return $this->CURD->insertUpdateSmarty($this->sTable, $aKVMap, $aUpdateKey);
    }

    /**
     * @param array $aData
     *
     * @return Item
     */
    public function createItem(array $aData = array())
    {
        return new $this->sItemClassName($aData, $this);
    }

    /**
     * @param array $aKVMap
     *
     * @return bool
     */
    public function add($aKVMap)
    {
        return $this->createItem($aKVMap)->add();
    }

    /**
     * @param mixed $mPKOrWhere
     *
     * @return bool
     */
    public function delete($mPKOrWhere)
    {
        $Model = $this->find($mPKOrWhere);
        if ($Model === null) {
            return false;
        }
        return $Model->delete();
    }

    /**
     * @param mixed $mPKOrWhere
     * @param array $aKVMap
     *
     * @return bool|int
     */
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
        return empty($aItem) ? null : new $this->sItemClassName($aItem, $this);
    }

    /**
     * @param array  $aWhere
     * @param string $sOrderBy
     * @param int    $iLimit
     * @param int    $iOffset
     * @param string $sTable
     *
     * @return Group|Item[]
     */
    public function findMulti(array $aWhere = null, $sOrderBy = null, $iLimit = null, $iOffset = null, $sTable = null)
    {
        $sAttr = '';
        $sOrderBy !== null && $sAttr .= " ORDER BY $sOrderBy";
        $iLimit !== null && $sAttr .= " LIMIT $iLimit";
        $iOffset !== null && $sAttr .= " OFFSET $iOffset";

        $aaData = $this->CURD->querySmarty(
            $sTable === null ? $this->sTable : $sTable,
            $aWhere,
            $sAttr
        );

        $Group = new Group($this);

        if (empty($aaData)) {
            return $Group;
        }
        foreach ($aaData as $aRow) {
            $Group[$aRow[$this->sPKName]] = new $this->sItemClassName($aRow, $this, $Group);
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
     * @param string $sTable
     * @param bool   $bOnlyOne
     * @param int    $iFetchStyle
     * @param mixed  $mFetchArgs
     *
     * @return array|bool|mixed
     */
    public function findCustom(
        $aWhere = array(),
        $sAttr = '',
        $sSelect = '',
        $sTable  = null,
        $bOnlyOne = false,
        $iFetchStyle = \PDO::FETCH_ASSOC,
        $mFetchArgs = null
    ) {
        return $this->CURD->querySmarty($sTable===null ? $this->sTable : $sTable, $aWhere, $sAttr, $sSelect, $bOnlyOne, $iFetchStyle, $mFetchArgs);
    }
}
