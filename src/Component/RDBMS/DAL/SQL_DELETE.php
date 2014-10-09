<?php
namespace Slime\Component\RDBMS\DAL;

/**
 * Class SQL_DELETE
 *
 * @package Slime\Component\RDBMS\DAL
 * @author  smallslime@gmail.com
 */
class SQL_DELETE extends SQL
{
    public function __toString()
    {
        if ($this->nsSQL===null) {
            $this->nsSQL = sprintf(
                "DELETE FROM %s%s%s%s%s",
                $this->parseTable($this->sTable_SQLSEL),
                ($nsJoin = $this->parseJoin()) === null ? '' : " $nsJoin",
                $this->naWhere === null ? '' : ' WHERE ' . $this->parseCondition($this->naWhere),
                $this->naOrder === null ? '' : ' ORDER BY ' . implode(' ', $this->naOrder),
                $this->niLimit === null ? '' : " LIMIT {$this->niLimit}",
                $this->niOffset === null ? '' : " OFFSET {$this->niOffset}"
            );
        }

        return $this->nsSQL;
    }
}