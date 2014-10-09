<?php
namespace Slime\Component\RDBMS\DAL;

/**
 * Class SQL_INSERT
 *
 * @package Slime\Component\RDBMS\DAL
 * @author  smallslime@gmail.com
 */
class SQL_INSERT extends SQL
{
    /**
     * @var null | SQL_SELECT
     */
    protected $nSEL = null;

    /**
     * @param SQL_SELECT $SEL
     *
     * @return $this
     */
    public function setSubSEL($SEL)
    {
        $this->nSEL = $SEL;
        return $this;
    }

    const TYPE_IGNORE = 1;
    const TYPE_UPDATE = 2;
    const TYPE_REPLACE = 3;

    protected $niType = null;
    protected $naWhere = null;

    /**
     * @param int          $iType   SQL_INSERT::TYPE_IGNORE / SQL_INSERT::TYPE_UPDATE / SQL_INSERT::TYPE_REPLACE
     * @param null | array $naWhere if iType is TYPE_UPDATE , declare as condition(kv map)
     */
    public function setType($iType, $naWhere = null)
    {
        $this->niType = $iType;
        if ($naWhere !== null) {
            $this->naWhere = $naWhere;
        }
    }

    protected $naKey;
    protected $aData = array();

    /**
     * @param array $aKV
     *
     * @return $this
     */
    public function addData($aKV)
    {
        if ($this->naKey === null && is_string(key($aKV))) {
            $this->naKey = array_keys($aKV);
        }
        $this->aData[] = $aKV;

        return $this;
    }

    /**
     * @param array $aKey
     *
     * @return $this
     */
    public function setKey($aKey)
    {
        $this->naKey = $aKey;

        return $this;
    }

    protected function parseData()
    {
        if ($this->nSEL !== null) {
            return (string)$this->nSEL;
        }

        $aTidy = array();
        foreach ($this->aData as $aRow) {
            $aV = array();
            foreach ($aRow as $mV) {
                $aV[] = is_string($mV) ? "'$mV'" : (string)$mV;
            }
            $aTidy[] = implode(',', $aV);
        }

        switch (count($aTidy)) {
            case 0:
                return null;
            case 1:
                return $aTidy[0];
            default:
                return implode('),(', $aTidy);
        }
    }

    public function __toString()
    {
        if ($this->nsSQL === null) {
            $this->nsSQL = sprintf(
                "%s INTO %s%s VALUES (%s)%s",
                $this->niType === self::TYPE_IGNORE ? 'INSERT IGNORE' : ($this->niType === self::TYPE_REPLACE ? 'REPLACE' : 'INSERT'),
                $this->parseTable($this->sTable_SQLSEL),
                $this->naKey === null ? '' : (' (`' . implode('`,`', $this->naKey) . '`)'),
                $this->parseData(),
                $this->niType === self::TYPE_UPDATE ? (' ON DUPLICATE KEY UPDATE ' . $this->parseCondition(
                        $this->naWhere
                    )) : ''
            );
        }

        return $this->nsSQL;
    }
}