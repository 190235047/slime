<?php
namespace Slime\Component\RDBMS\ORM;

use Slime\Component\RDBMS\DBAL\Engine;
use Slime\Component\RDBMS\DBAL\Bind;
use Slime\Component\RDBMS\DBAL\BindItem;
use Slime\Component\RDBMS\DBAL\Condition;
use Slime\Component\RDBMS\DBAL\SQL;
use Slime\Component\RDBMS\DBAL\SQL_DELETE;
use Slime\Component\RDBMS\DBAL\SQL_INSERT;
use Slime\Component\RDBMS\DBAL\SQL_SELECT;
use Slime\Component\RDBMS\DBAL\V;

/**
 * Class Model
 *
 * @package Slime\Component\RDBMS\ORM
 * @author  smallslime@gmail.com
 *
 * @property-read string     $sMName
 * @property-read string     $sTable
 * @property-read string     $sPKName
 * @property-read string     $sFKName
 * @property-read array      $aRelConf
 * @property-read null|array $naField
 * @property-read bool       $bUseFull
 *
 * @property-read Engine     $Engine
 * @property-read Factory    $Factory
 * @property-read string     $sItemClass
 */
class Model
{
    protected $Factory;
    protected $Engine;

    protected $sMName;

    protected $sItemClass;
    protected $sTable;
    protected $sPKName;
    protected $sFKName;
    protected $aRelConf;
    protected $naField;
    protected $bUseFull;

    protected $nsFKNameTmp = null;

    public function __get($sK)
    {
        return $this->$sK;
    }

    /**
     * @param string  $sItemClass
     * @param string  $sMName
     * @param Engine  $Engine
     * @param array   $aConf
     * @param Factory $Factory
     *
     * @throws \RuntimeException
     */
    public function __construct($sItemClass, $sMName, $Engine, $aConf, $Factory)
    {
        $this->sMName     = $sMName;
        $this->Engine     = $Engine;
        if ($this->sItemClass === null) {
            $this->sItemClass = $sItemClass;
        }
        if ($this->sTable === null) {
            $this->sTable = isset($aConf['table']) ? $aConf['table'] : strtolower($sMName);
        }
        if ($this->sPKName === null) {
            $this->sPKName = isset($aConf['pk']) ? $aConf['pk'] : 'id';
        }
        if ($this->sFKName === null) {
            $this->sFKName = isset($aConf['fk']) ? $aConf['fk'] : $this->sTable . '_id';
        }
        if ($this->aRelConf === null) {
            $this->aRelConf = isset($aConf['relation']) ? $aConf['relation'] : array();
        }
        if ($this->naField === null) {
            $this->naField = isset($aConf['fields']) ? $aConf['fields'] : null;
        }
        if ($this->bUseFull === null) {
            $this->bUseFull = !empty($aConf['use_full_field_in_select']);
        }
        $this->Factory = $Factory;
    }

    public function SQL_INS()
    {
        return SQL::INS($this->sTable);
    }

    public function SQL_UPD()
    {
        return SQL::UPD($this->sTable);
    }

    public function SQL_SEL()
    {
        return SQL::SEL($this->sTable, $this->bUseFull ? $this->naField : null);
    }

    public function SQL_DEL()
    {
        return SQL::DEL($this->sTable);
    }

    /**
     * @param string $sFKName
     */
    public function setFKTmp($sFKName)
    {
        $this->nsFKNameTmp = $this->sFKName;
        $this->sFKName     = $sFKName;
    }

    public function resetFK()
    {
        if ($this->nsFKNameTmp !== null) {
            $this->sFKName     = $this->nsFKNameTmp;
            $this->nsFKNameTmp = null;
        }
    }

    /**
     * @param array | SQL_INSERT $m_aKVData_SQL
     * @param null | Bind        $m_n_Bind
     *
     * @return bool | int
     */
    public function insert($m_aKVData_SQL, $m_n_Bind = null)
    {
        return $this->Engine->E(
            $SQL = is_array($m_aKVData_SQL) ?
                $this->SQL_INS()->values($m_aKVData_SQL) :
                $m_aKVData_SQL,
            $m_n_Bind
        ) ? $this->Engine->inst($SQL)->lastInsertId() : false;
    }

    /**
     * @param null | string | int | Condition | SQL $m_n_siPK_Condition_SQL
     * @param array                                 $aKVData
     * @param null | Bind                           $m_n_Bind
     *
     * @return bool | int
     */
    public function update($m_n_siPK_Condition_SQL, array $aKVData, $m_n_Bind = null)
    {
        if ($m_n_siPK_Condition_SQL instanceof SQL_DELETE) {
            $SQL = $m_n_siPK_Condition_SQL;
        } else {
            $SQL = $this->SQL_UPD();
            if ($m_n_siPK_Condition_SQL !== null) {
                $SQL->where(
                    $m_n_siPK_Condition_SQL instanceof Condition ?
                        $m_n_siPK_Condition_SQL :
                        Condition::build()->set($this->sPKName, '=', $m_n_siPK_Condition_SQL)
                );
            }
        }
        $SQL->setMulti($aKVData);

        return $this->Engine->E($SQL, $m_n_Bind);
    }

    /**
     * @param null | string | int | Condition | SQL $m_n_siPK_Condition_SQL
     * @param null | Bind                           $m_n_Bind
     *
     * @return bool
     */
    public function delete($m_n_siPK_Condition_SQL, $m_n_Bind = null)
    {
        if ($m_n_siPK_Condition_SQL instanceof SQL_DELETE) {
            $SQL = $m_n_siPK_Condition_SQL;
        } else {
            $SQL = $this->SQL_DEL();
            if ($m_n_siPK_Condition_SQL !== null) {
                $SQL->where(
                    $m_n_siPK_Condition_SQL instanceof Condition ?
                        $m_n_siPK_Condition_SQL :
                        Condition::build()->set($this->sPKName, '=', $m_n_siPK_Condition_SQL)
                );
            }
        }

        return $this->Engine->E($SQL, $m_n_Bind);
    }

    /**
     * @param Condition | SQL_SELECT | string | int $m_n_siPK_Condition_SQL
     * @param Bind                                  $m_n_Bind
     *
     * @return Item | CItem | null
     */
    public function find($m_n_siPK_Condition_SQL, $m_n_Bind = null)
    {
        if ($m_n_siPK_Condition_SQL instanceof SQL_SELECT) {
            $SQL = $m_n_siPK_Condition_SQL;
        } else {
            $SQL = $this->SQL_SEL();
            if ($m_n_siPK_Condition_SQL !== null) {
                $SQL->where(
                    $m_n_siPK_Condition_SQL instanceof Condition ?
                        $m_n_siPK_Condition_SQL :
                        Condition::build()->set($this->sPKName, '=', $m_n_siPK_Condition_SQL)
                );
            }
        }
        $SQL->limit(1);
        $mItem = $this->Engine->Q($SQL, $m_n_Bind);

        return empty($mItem) ? Factory::newNull() : new $this->sItemClass($mItem[0], $this);
    }

    /**
     * @param Condition | SQL_SELECT | null | array $m_n_aPK_Condition_SQL
     * @param string | BindItem | array             $mOrderBy
     * @param null | int                            $niLimit
     * @param null | int                            $niOffset
     * @param null | Bind                           $m_n_Bind
     *
     * @return Group | Item[]
     */
    public function findMulti(
        $m_n_aPK_Condition_SQL = null,
        $mOrderBy = null,
        $niLimit = null,
        $niOffset = null,
        $m_n_Bind = null
    ) {
        $aaData = $this->findCustom($m_n_aPK_Condition_SQL, $mOrderBy, $niLimit, $niOffset, $m_n_Bind);

        $Group = new Group($this);
        if (empty($aaData)) {
            return $Group;
        }
        foreach ($aaData as $aRow) {
            $Group[$aRow[$this->sPKName]] = new $this->sItemClass($aRow, $this, $Group);
        }
        return $Group;
    }

    /**
     * @param Condition | SQL_SELECT | null $m_n_aPK_Condition_SQL
     * @param null | Bind                   $m_n_Bind
     *
     * @return int | bool
     */
    public function findCount($m_n_aPK_Condition_SQL = null, $m_n_Bind = null)
    {
        if ($m_n_aPK_Condition_SQL instanceof SQL_SELECT) {
            $SQL = $m_n_aPK_Condition_SQL;
        } else {
            $SQL = $this->SQL_SEL();
            if ($m_n_aPK_Condition_SQL !== null) {
                if (is_array($m_n_aPK_Condition_SQL)) {
                    $SQL->where(Condition::build()->set($this->sPKName, 'IN', $m_n_aPK_Condition_SQL));
                } else {
                    $SQL->where($m_n_aPK_Condition_SQL);
                }
            }
        }
        $SQL->fields(V::make('count(1) AS total'))->limit(1);
        $aItem = $this->Engine->Q(
            $SQL,
            $m_n_Bind
        );

        return $aItem === false ? false : $aItem[0]['total'];
    }

    /**
     * @param Condition | SQL_SELECT | null $m_n_Condition_SQL
     * @param string | BindItem | array     $mOrderBy
     * @param int                           $niLimit
     * @param int                           $niOffset
     * @param Bind | null                   $m_n_Bind
     *
     * @return bool | array
     */
    public function findCustom(
        $m_n_Condition_SQL = null,
        $mOrderBy = null,
        $niLimit = null,
        $niOffset = null,
        $m_n_Bind = null
    ) {
        if ($m_n_Condition_SQL instanceof SQL_SELECT) {
            $aaData = $this->Engine->Q($m_n_Condition_SQL, $m_n_Bind);
        } else {
            $SQL = $this->SQL_SEL();
            if ($m_n_Condition_SQL !== null) {
                $SQL->where($m_n_Condition_SQL);
            }
            if ($mOrderBy !== null) {
                if (is_array($mOrderBy)) {
                    foreach ($mOrderBy as $mItem) {
                        $SQL->orderBy($mItem);
                    }
                } else {
                    $SQL->orderBy($mOrderBy);
                }
            }
            if ($niLimit !== null) {
                $SQL->limit($niLimit);
            }
            if ($niOffset !== null) {
                $SQL->offset($niOffset);
            }
            $aaData = $this->Engine->Q($SQL, $m_n_Bind);
        }

        return $aaData;
    }
}
