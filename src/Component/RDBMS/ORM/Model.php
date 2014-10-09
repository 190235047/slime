<?php
namespace Slime\Component\RDBMS\ORM;

use Slime\Component\RDBMS\DAL\SQL;
use Slime\Component\RDBMS\DAL\SQL_SELECT;
use Slime\Component\RDBMS\DAL\Val;

/**
 * Class Model
 *
 * @package Slime\Component\RDBMS\ORM
 * @author  smallslime@gmail.com
 */
class Model
{
    protected $sItemClassNS = null;
    protected $sItemClassPartName = null;

    private $sItemClassName;

    public $sModelName;

    public $DAL;
    public $sTable;
    public $sPKName;
    public $sFKName;
    public $aRelationConfig;
    public $Factory;

    protected $sFKNameTmp = null;

    /**
     * @param string                            $sModelName
     * @param \Slime\Component\RDBMS\DAL\Engine $DAL
     * @param array                             $aConfig
     * @param Factory                           $Factory
     *
     * @throws \RuntimeException
     */
    public function __construct($sModelName, $DAL, $aConfig, $Factory)
    {
        $this->sModelName      = $sModelName;
        $this->DAL             = $DAL;
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
            if ($sCalledNS === 'Slime\\Component\\RDBMS\\ORM' && $sCalledPartClassName === 'Model') {
                $this->sItemClassPartName = 'Item';
            } elseif (substr($sCalledPartClassName, 0, 6) === 'Model_') {
                $this->sItemClassPartName = 'Item_' . substr($sCalledPartClassName, 6);
            } else {
                throw new \RuntimeException(
                    '[MODEL] : Can not parse item class name through both automatic and defined'
                );
            }
        }
        $this->sItemClassName = $this->sItemClassNS . '\\' . $this->sItemClassPartName;
    }

    public function SQL_C()
    {
        return SQL::C($this->sTable);
    }

    public function SQL_U()
    {
        return SQL::U($this->sTable);
    }

    public function SQL_R()
    {
        return SQL::R($this->sTable);
    }

    public function SQL_D()
    {
        return SQL::D($this->sTable);
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
     * @param bool  $bLastID
     * @param null  $iLastID
     *
     * @return bool
     */
    public function add(array $aKVMap, $bLastID = false, &$iLastID = null)
    {
        $SQL = $this->SQL_C()->addData($aKVMap);
        $bRS = $this->DAL->E($SQL);
        if ($bLastID && $bRS!==false) {
            $iLastID = $this->DAL->getInstance($SQL)->lastInsertId();
        }

        return $bRS;
    }

    /**
     * @var \Slime\Component\RDBMS\DAL\SQL_INSERT
     */
    protected $SQL_BUFFER;
    protected $iBufferMax;
    protected $iBufferCount;

    /**
     * @param array $aKey
     * @param int   $iMax
     */
    public function addBuffer_Start($aKey, $iMax = 100)
    {
        $this->SQL_BUFFER = $this->SQL_C();
        $this->SQL_BUFFER->setKey($aKey);
        $this->iBufferMax = $iMax;
    }

    /**
     * @param array $aData
     */
    public function addBuffer(array $aData)
    {
        if (++$this->iBufferCount >= $this->iBufferMax) {
            $this->flushBuffer();
        }
        $this->SQL_BUFFER->addData($aData);
    }

    public function addBuffer_End()
    {
        $this->flushBuffer();
    }

    protected function flushBuffer()
    {
        $this->DAL->E($this->SQL_BUFFER);
        $this->iBufferCount = 0;
    }

    /**
     * @param mixed $mPKOrWhere
     * @param array $aKVMap
     *
     * @return bool | int
     */
    public function update($mPKOrWhere, $aKVMap)
    {
        return $this->DAL->E(
            $this->SQL_U()
                ->setKV($aKVMap)
                ->where(is_array($mPKOrWhere) ? $mPKOrWhere : array($this->sPKName => $mPKOrWhere))
        );
    }

    /**
     * @param mixed $mPKOrWhere
     *
     * @return bool
     */
    public function delete($mPKOrWhere)
    {
        return $this->DAL->E(
            $this->SQL_D()
                ->where(is_array($mPKOrWhere) ? $mPKOrWhere : array($this->sPKName => $mPKOrWhere))
        );
    }

    /**
     * @param mixed $mPKOrWhere
     *
     * @return Item | CItem | null
     */
    public function find($mPKOrWhere)
    {
        $mItem = $this->DAL->Q(
            $this->SQL_R()
                ->where(is_array($mPKOrWhere) ? $mPKOrWhere : array($this->sPKName => $mPKOrWhere))
                ->limit(1)
        );

        return empty($mItem) ?
            ($this->Factory->isCompatibleMode() ? new CItem() : null) :
            new $this->sItemClassName($mItem[0], $this);
    }

    /**
     * @param array | SQL_SELECT $aWhere_SQLSEL
     * @param string             $nsOrderBy
     * @param int                $niLimit
     * @param int                $niOffset
     *
     * @return Group | Item[]
     */
    public function findMulti(
        array $aWhere_SQLSEL = array(),
        $nsOrderBy = null,
        $niLimit = null,
        $niOffset = null
    ) {
        if (is_array($aWhere_SQLSEL)) {
            $SQL = $this->SQL_R();
            if ($aWhere_SQLSEL !== null) {
                $SQL->where($aWhere_SQLSEL);
            }
            if ($nsOrderBy !== null) {
                $SQL->orderBy($nsOrderBy);
            }
            if ($niLimit !== null) {
                $SQL->limit($niLimit);
            }
            if ($niOffset !== null) {
                $SQL->offset($niOffset);
            }
            $aaData = $this->DAL->Q($SQL);
        } else {
            $aaData = $this->DAL->Q($aWhere_SQLSEL);
        }

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
     * @param array | SQL_SELECT $aWhere_SQLSEL
     *
     * @return int | bool
     */
    public function findCount($aWhere_SQLSEL = null)
    {
        $aItem = $this->DAL->Q(
            is_array($aWhere_SQLSEL) ?
                $this->SQL_R()
                    ->where($aWhere_SQLSEL)
                    ->fields(Val::Keyword('count(1) AS total'))
                : $aWhere_SQLSEL
        );

        return $aItem === false ? false : $aItem[0]['total'];
    }

    /**
     * @param array | SQL_SELECT $aWhere_SQLSEL
     * @param string             $nsOrderBy
     * @param int                $niLimit
     * @param int                $niOffset
     *
     * @return bool | array
     */
    public function findCustom(
        array $aWhere_SQLSEL = array(),
        $nsOrderBy = null,
        $niLimit = null,
        $niOffset = null
    ) {
        if (is_array($aWhere_SQLSEL)) {
            $SQL = $this->SQL_R();
            if ($aWhere_SQLSEL !== null) {
                $SQL->where($aWhere_SQLSEL);
            }
            if ($nsOrderBy !== null) {
                $SQL->orderBy($nsOrderBy);
            }
            if ($niLimit !== null) {
                $SQL->limit($niLimit);
            }
            if ($niOffset !== null) {
                $SQL->offset($niOffset);
            }
            $aaData = $this->DAL->Q($SQL);
        } else {
            $aaData = $this->DAL->Q($aWhere_SQLSEL);
        }

        return $aaData;
    }
}
