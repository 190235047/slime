<?php
namespace Slime\Component\RDBMS\DAL;

/**
 * Class SQL_SELECT
 *
 * @package Slime\Component\RDBMS\DAL
 * @author  smallslime@gmail.com
 */
class SQL_SELECT extends SQL
{
    protected $naGroupBy = null;
    protected $naField = null;

    /**
     * @param string | Val $sField_Val
     *
     * multi param as param one
     *
     * @return $this
     */
    public function fields($sField_Val)
    {
        $aArr          = func_get_args();
        $this->naField = $this->naField === null ? $aArr : array_merge($this->naField, $aArr);
        return $this;
    }

    /**
     * @param string | Val $sGroupBy_Val
     *
     * multi param as param one
     *
     * @return $this
     */
    public function groupBy($sGroupBy_Val)
    {
        $aArr            = func_get_args();
        $this->naGroupBy = $this->naGroupBy === null ? $aArr : array_merge($this->naGroupBy, $aArr);
        return $this;
    }

    public function __toString()
    {
        if ($this->nsSQL === null) {
            $this->nsSQL = sprintf(
                "SELECT %s FROM %s%s%s%s%s%s%s",
                $this->naField === null ? '*' : implode(',', $this->naField),
                $this->parseTable($this->sTable_SQLSEL),
                ($nsJoin = $this->parseJoin()) === null ? '' : " $nsJoin",
                $this->naWhere === null ? '' : ' WHERE ' . $this->parseCondition($this->naWhere),
                $this->naGroupBy === null ? '' : ' GROUP BY ' . implode(',', $this->naGroupBy),
                ($nsOrder = $this->parseOrder()) === null ? '' : " ORDER BY {$nsOrder}",
                $this->niLimit === null ? '' : " LIMIT {$this->niLimit}",
                $this->niOffset === null ? '' : " OFFSET {$this->niOffset}"
            );
        }

        return $this->nsSQL;
    }

}
