<?php
namespace Slime\Component\RDBMS\DBAL;

/**
 * Class SQL
 *
 * @package Slime\Component\RDBMS\DBAL
 * @author  smallslime@gmail.com
 *
 * @property-read string           $sTable
 * @property-read null | array     $naJoin
 * @property-read null | Condition $nWhere
 * @property-read null | array     $naOrder
 * @property-read null | int       $niLimit
 * @property-read null | int       $niOffset
 */
abstract class SQL
{
    /**
     * @param string|SQL_SELECT $sTable_SQLSEL
     * @param null|array        $naDFTField
     *
     * @return SQL_SELECT
     */
    public static function SEL($sTable_SQLSEL, array $naDFTField = null)
    {
        return new SQL_SELECT($sTable_SQLSEL, $naDFTField);
    }

    /**
     * @param string | SQL_SELECT $sTable_SQLSEL
     *
     * @return SQL_INSERT
     */
    public static function INS($sTable_SQLSEL)
    {
        return new SQL_INSERT($sTable_SQLSEL);
    }

    /**
     * @param string | SQL_SELECT $sTable_SQLSEL
     *
     * @return SQL_UPDATE
     */
    public static function UPD($sTable_SQLSEL)
    {
        return new SQL_UPDATE($sTable_SQLSEL);
    }

    /**
     * @param string | SQL_SELECT $sTable_SQLSEL
     *
     * @return SQL_DELETE
     */
    public static function DEL($sTable_SQLSEL)
    {
        return new SQL_DELETE($sTable_SQLSEL);
    }

    //----------------

    /** @var string */
    protected $sTable;
    /** @var null|array */
    protected $naJoin = null;
    /** @var null|Condition */
    protected $nWhere = null;
    /** @var null|array */
    protected $naOrder = null;
    /** @var null|int */
    protected $niLimit = null;
    /** @var null|int */
    protected $niOffset = null;
    /** @var null | string */
    protected $m_n_sSQL = null;

    /** @var null | Bind */
    protected $m_n_Bind = null;

    /**
     * @param Bind $Bind
     */
    public function setBind($Bind)
    {
        $this->m_n_Bind = $Bind;
    }

    protected $aBindField;

    public function getBindFields()
    {
        return $this->aBindField;
    }

    /**
     * @param \PDOStatement $STMT
     *
     * @throws \InvalidArgumentException
     */
    public function bind($STMT)
    {
        if ($STMT === false || $this->m_n_Bind === null) {
            throw new \RuntimeException("[DBAL] : Can not do bind!");
        }
        $this->m_n_Bind->bind($this, $STMT);
    }

    public function isNeedPrepare()
    {
        $this->build();
        return $this->m_n_Bind !== null;
    }

    abstract protected function build();

    public function __toString()
    {
        if ($this->m_n_sSQL === null) {
            $this->build();
        }

        return $this->m_n_sSQL;
    }

    /**
     * @param string $sTable
     */
    public function __construct($sTable)
    {
        $this->sTable = $sTable;
    }

    public function __get($sK)
    {
        return $this->$sK;
    }

    /**
     * @param Condition $Condition
     *
     * @return $this
     */
    public function where($Condition)
    {
        $this->m_n_sSQL !== null && $this->m_n_sSQL = null;
        $this->nWhere = $Condition;

        return $this;
    }

    /**
     * @param string | SQL_SELECT $sTable_SQLSEL
     * @param Condition           $Condition
     * @param string              $sJoinType
     *
     * @return $this
     */
    public function join($sTable_SQLSEL, $Condition, $sJoinType = 'INNER')
    {
        $this->m_n_sSQL !== null && $this->m_n_sSQL = null;
        $this->naJoin[] = array($sJoinType, $sTable_SQLSEL, $Condition);

        return $this;
    }

    /**
     * @param string | BindItem $m_sOrder_BindItem
     *
     * @return $this
     */
    public function orderBy($m_sOrder_BindItem)
    {
        $this->naOrder[] = $m_sOrder_BindItem;

        return $this;
    }

    /**
     * @param int $iLimit
     *
     * @return $this
     */
    public function limit($iLimit)
    {
        $this->niLimit = $iLimit;

        return $this;
    }

    /**
     * @param int $iOffset
     *
     * @return $this
     */
    public function offset($iOffset)
    {
        $this->niOffset = $iOffset;

        return $this;
    }


    protected function parseTable($sTable = null)
    {
        $sTable = $sTable === null ? $this->sTable : $sTable;
        return strpos($sTable, '.') === false ? "`{$sTable}`" : $sTable;
    }

    /**
     * @param Condition $Condition
     *
     * @return string
     */
    protected function parseCondition($Condition)
    {
        if (count($Condition->aData) === 0) {
            return '1';
        }
        $aRS = array();
        foreach ($Condition->aData as $mItem) {
            if ($mItem instanceof Condition) {
                $aRS[] = "({$mItem})";
                continue;
            }

            // expr value
            $mV = $mItem[2];
            if (is_array($mV)) {
                // IN [1,2,3,4,5...]
                $aTidy = array();
                foreach ($mV as $mOne) {
                    if ($mOne instanceof BindItem) {
                        $this->aBindField[$mOne->sK] = $mOne->sK;
                        if ($this->m_n_Bind === null) {
                            $this->m_n_Bind = $mOne->Bind;
                        }
                        $aTidy[] = (string)$mOne;
                    } else {
                        $aTidy[] = is_string($mOne) ? "'$mOne'" : (string)$mOne;
                    }
                }
                $sStr = '(' . implode(',', $aTidy) . ')';
            } elseif ($mV instanceof BindItem) {
                $this->aBindField[$mV->sK] = $mV->sK;
                if ($this->m_n_Bind === null) {
                    $this->m_n_Bind = $mV->Bind;
                }
                $sStr = (string)$mV;
            } else {
                $sStr = is_string($mV) ? "'{$mV}'" : $mV;
            }

            $aRS[] = sprintf(
                '%s %s %s',
                is_string($mItem[0]) && strpos($mItem[0], '.') === false ? "`{$mItem[0]}`" : $mItem[0],
                $mItem[1],
                $sStr
            );
        }

        return implode(" $Condition->sRel ", $aRS);
    }

    protected function parseJoin()
    {
        if ($this->naJoin === null) {
            return null;
        }

        $aArr = array();
        foreach ($this->naJoin as $aRow) {
            $aArr[] = sprintf(
                "%s JOIN %s ON %s",
                $aRow[0],
                $this->parseTable($aRow[1]),
                $this->parseCondition($aRow[2])
            );
        }

        return implode(' ', $aArr);
    }

    protected function parseOrder()
    {
        if ($this->naOrder === null) {
            return null;
        }

        $aTidy = array();
        foreach ($this->naOrder as $mItem) {
            if ($mItem instanceof BindItem) {
                if ($this->m_n_Bind === null) {
                    $this->m_n_Bind = $mItem->Bind;
                }
                $this->aBindField[$mItem->sK] = $mItem->sK;

                if ($mItem->mAttr === null) {
                    $sSort = 'ASC';
                } else {
                    $sS    = strtoupper($mItem->mAttr);
                    $sSort = ($sS === '-' || $sS === 'DESC') ? 'DESC' : 'ASC';
                }
                $mItem->changeV($mItem->mV . " $sSort");
                $aTidy[] = (string)$mItem;
            } elseif (is_string($mItem)) {
                $sSort = $mItem[0] === '-' ? 'DESC' : 'ASC';
                if ($mItem[0] === '-' || $mItem[0] === '+') {
                    $mItem = substr($mItem, 1);
                }
                $mItem   = strpos($mItem, '.') === false ? "`{$mItem}`" : $mItem;
                $aTidy[] = "$mItem $sSort";
            } else {
                throw new \RuntimeException('[DBAL] : SQL order by parse error : ' . json_encode($mItem));
            }
        }

        $sOrder = implode(',', $aTidy);
        return $sOrder;
    }

    protected function parseLimit()
    {
        return $this->__parse_limit_offset($this->niLimit);
    }

    protected function parseOffset()
    {
        return $this->__parse_limit_offset($this->niOffset);
    }

    protected function __parse_limit_offset($mV)
    {
        if ($mV instanceof BindItem) {
            $this->aBindField[$mV->sK] = $mV->sK;
            if ($this->m_n_Bind === null) {
                $this->m_n_Bind = $mV->Bind;
            }

            return (string)$mV;
        } else {
            return $mV === null ? null : (int)$mV;
        }
    }
}
