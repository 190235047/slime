<?php
namespace Slime\Component\RDBMS\DAL;

/**
 * Class SQL_UPDATE
 *
 * @package Slime\Component\RDBMS\DAL
 * @author  smallslime@gmail.com
 */
class SQL_UPDATE extends SQL
{
    protected $aMap = array();

    /**
     * @param string $sK
     * @param mixed  $mV string | int | float | Val
     *
     * @return $this
     */
    public function set($sK, $mV)
    {
        $this->aMap[$sK] = $mV;
        return $this;
    }

    /**
     * @param array $aKV
     *
     * @return $this
     */
    public function setKV($aKV)
    {
        $this->aMap = array_merge($this->aMap, $aKV);

        return $this;
    }

    public function __toString()
    {
        if ($this->nsSQL === null) {
            $aTidy = array();
            foreach ($this->aMap as $sK => $mV) {
                $aTidy[] = "`$sK` = " . (is_string($mV) ? "'$mV'" : (string)$mV);
            }

            $this->nsSQL = sprintf(
                'UPDATE %s%s SET %s%s%s%s%s',
                $this->parseTable($this->sTable_SQLSEL),
                ($nsJoin = $this->parseJoin()) === null ? '' : " $nsJoin",
                implode(',', $aTidy),
                $this->naWhere === null ? '' : ' WHERE ' . $this->parseCondition($this->naWhere),
                ($nsOrder = $this->parseOrder()) === null ? '' : " ORDER BY {$nsOrder}",
                $this->niLimit === null ? '' : " LIMIT {$this->niLimit}",
                $this->niOffset === null ? '' : " OFFSET {$this->niOffset}"
            );
        }

        return $this->nsSQL;
    }
}