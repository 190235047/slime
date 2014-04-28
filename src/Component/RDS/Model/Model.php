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
    protected $sItemClassNS = null;
    protected $sItemClassPartName = null;

    private $sItemClassName;

    public $sModelName;

    public $CURD;
    public $sTable;
    public $sPKName;
    public $sFKName;
    public $aRelationConfig;
    public $Factory;

    protected $sFKNameTmp = null;

    /**
     * @param string  $sModelName
     * @param CURD    $CURD
     * @param array   $aConfig
     * @param Factory $Factory
     *
     * @throws \RuntimeException
     */
    public function __construct($sModelName, $CURD, $aConfig, $Factory)
    {
        $this->sModelName      = $sModelName;
        $this->CURD            = $CURD;
        $this->sTable          = isset($aConfig['table']) ? $aConfig['table'] : strtolower($sModelName);
        $this->sPKName         = isset($aConfig['pk']) ? $aConfig['pk'] : 'id';
        $this->sFKName         = isset($aConfig['fk']) ? $aConfig['fk'] : $this->sTable . '_id';
        $this->aRelationConfig = isset($aConfig['relation']) ? $aConfig['relation'] : array();
        $this->Factory         = $Factory;

        # auto generate item class
        $sCalledClassName = trim(get_called_class(), '\\');
        $iPos             = strrpos($sCalledClassName, '\\');
        if ($iPos === false) {
            $sCalledNS = $sCalledPartClassName = '';
        } else {
            $sCalledNS            = substr($sCalledClassName, 0, $iPos);
            $sCalledPartClassName = substr($sCalledClassName, $iPos + 1);
        }
        if ($this->sItemClassNS === null) {
            $this->sItemClassNS = $sCalledNS;
        }
        if ($this->sItemClassPartName === null) {
            if ($sCalledNS==='Slime\\Component\\RDS\\Model' && $sCalledPartClassName === 'Model') {
                $this->sItemClassPartName = 'Item';
            } elseif (substr($sCalledPartClassName, 0, 6) === 'Model_') {
                $this->sItemClassPartName = 'Item_' . substr($sCalledPartClassName, 6);
            } else {
                throw new \RuntimeException('[MODEL] : Can not parse item class name through both automatic and defined');
            }
        }
        $this->sItemClassName = $this->sItemClassNS . '\\' . $this->sItemClassPartName;
    }

    /**
     * @param string $sFKName
     */
    public function setFKTmp($sFKName)
    {
        $this->sFKNameTmp = $this->sFKName;
        $this->sFKName    = $sFKName;
    }

    public function resetFK()
    {
        if ($this->sFKNameTmp !== null) {
            $this->sFKName    = $this->sFKNameTmp;
            $this->sFKNameTmp = null;
        }
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
        $Item = $this->find($mPKOrWhere);
        if ($Item === null) {
            return false;
        }
        $Item->set($aKVMap);
        return $Item->update();
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
        return empty($aItem) ?
            ($this->Factory->isCompatibleMode() ? new CompatibleItem() : null) :
            new $this->sItemClassName($aItem, $this);
    }

    /**
     * @param array  $aWhere
     * @param string $sOrderBy
     * @param int    $iLimit
     * @param int    $iOffset
     * @param string $sTable
     * @param string $sSelect
     *
     * @return Group|Item[]
     */
    public function findMulti(
        array $aWhere = null,
        $sOrderBy = null,
        $iLimit = null,
        $iOffset = null,
        $sTable = null,
        $sSelect = null
    ) {
        $sAttr = '';
        $sOrderBy !== null && $sAttr .= " ORDER BY $sOrderBy";
        $iLimit !== null && $sAttr .= " LIMIT $iLimit";
        $iOffset !== null && $sAttr .= " OFFSET $iOffset";

        $aaData = $this->CURD->querySmarty(
            $sTable === null ? $this->sTable : $sTable,
            $aWhere,
            $sAttr,
            $sSelect
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
     * @param array  $aWhere
     * @param string $sTable
     *
     * @return int
     */
    public function findCount($aWhere = array(), $sTable = null)
    {
        return $this->CURD->queryCount($sTable === null ? $this->sTable : $sTable, $aWhere);
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
        $sTable = null,
        $bOnlyOne = false,
        $iFetchStyle = \PDO::FETCH_ASSOC,
        $mFetchArgs = null
    ) {
        return $this->CURD->querySmarty(
            $sTable === null ? $this->sTable : $sTable,
            $aWhere,
            $sAttr,
            $sSelect,
            $bOnlyOne,
            $iFetchStyle,
            $mFetchArgs
        );
    }
}
