<?php
namespace Slime\Component\RDBMS\DAL;

/**
 * Class SQL
 *
 * @package Slime\Component\RDBMS\DAL
 * @author  smallslime@gmail.com
 */
abstract class SQL
{
    /**
     * @param string | SQL_SELECT $sTable_SQLSEL
     *
     * @return SQL_SELECT
     */
    public static function R($sTable_SQLSEL)
    {
        return new SQL_SELECT($sTable_SQLSEL);
    }

    /**
     * @param string | SQL_SELECT $sTable_SQLSEL
     *
     * @return SQL_INSERT
     */
    public static function C($sTable_SQLSEL)
    {
        return new SQL_INSERT($sTable_SQLSEL);
    }

    /**
     * @param string | SQL_SELECT $sTable_SQLSEL
     *
     * @return SQL_UPDATE
     */
    public static function U($sTable_SQLSEL)
    {
        return new SQL_UPDATE($sTable_SQLSEL);
    }

    /**
     * @param string | SQL_SELECT $sTable_SQLSEL
     *
     * @return SQL_DELETE
     */
    public static function D($sTable_SQLSEL)
    {
        return new SQL_DELETE($sTable_SQLSEL);
    }

    //----------------
    protected $nsSQL = null;

    /**
     * @param string $sSQL
     *
     * @return $this
     */
    public function setCustomSQL($sSQL)
    {
        $this->nsSQL = $sSQL;

        return $this;
    }

    /**
     * @return $this
     */
    public function clear()
    {
        $this->nsSQL = null;
        return $this;
    }

    /** @var SQL_SELECT|string */
    protected $sTable_SQLSEL;
    /** @var null|array */
    protected $naJoin = null;
    /** @var null|array */
    protected $naWhere = null;
    /** @var null|array */
    protected $naOrder = null;
    /** @var null|int */
    protected $niLimit = null;
    /** @var null|int */
    protected $niOffset = null;
    /** @var null|string */
    protected $nsAlias = null;
    /** @var Bind */
    protected $Bind;

    /**
     * @param string | SQL_SELECT $sTable_SQLSEL
     */
    public function __construct($sTable_SQLSEL)
    {
        $this->sTable_SQLSEL = $sTable_SQLSEL;
        $this->Bind          = new Bind();

        return $this;
    }

    /**
     * @return SQL_SELECT|string
     */
    public function getTable()
    {
        return $this->sTable_SQLSEL;
    }

    /**
     * @return Bind
     */
    public function getBind()
    {
        return $this->Bind;
    }

    public function setBuffer()
    {
        ;
    }

    public function isBuffer()
    {
        ;
    }

    /**
     * @param array $aWhere
     *
     * @example : $SEL = SQL::R('User');
     *            $B   = $SEL->getBind();
     *            $B->set('status', 1, PDO::PARAM_INT)->set('name', 'abc')
     *            $aWhere = [
     *                'status'         => $B['status'],
     *                'create_time >=' => '2014-05-01 20:00:01',
     *                Val::Condition(Val::Name('title'), $B['title'], 'LIKE'),
     *                Val::Condition('type', Val::Name('sub_type')),
     *                Val::Condition(Val::Val(1), 1),
     *                [
     *                    'title LIKE' => "%oly_{$B['name']}%",
     *                    'type IN'    => array(1,2,3)
     *                ],
     *                'area IN'        => array('china', 'japan', 'korea'),
     *                -1               => 'OR'
     *            ]
     *            $SEL->where($aWhere);
     *
     * @return $this
     */
    public function where(array $aWhere)
    {
        $this->naWhere = $aWhere;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getWhere()
    {
        return $this->naWhere;
    }

    /**
     * @param mixed $mParameter
     * @param mixed $mValue
     * @param int   $iDataType
     *
     * @return $this
     */
    public function bind($mParameter, $mValue, $iDataType = \PDO::PARAM_STR)
    {
        $this->Bind[$mParameter] = array($mValue, $iDataType);

        return $this;
    }

    /**
     * @param string | SQL_SELECT $sTable_SQLSEL
     * @param array               $aCondition
     * @param string              $sJoinType
     *
     * @return $this
     */
    public function join($sTable_SQLSEL, array $aCondition, $sJoinType = 'INNER')
    {
        $this->naJoin[] = array($sTable_SQLSEL, $aCondition, $sJoinType);

        return $this;
    }

    /**
     * @return array|null
     */
    public function getJoin()
    {
        return $this->naJoin;
    }

    /**
     * @param string $sOrder_Val -id means id desc; id / +id means id asc;
     *
     * @return $this
     */
    public function orderBy($sOrder_Val)
    {
        $aArr = func_get_args();
        if (count($aArr) === 1) {
            $aBlock = explode(',', $aArr[0]);
            if (count($aBlock) > 1) {
                $aArr = $aBlock;
            }
        }
        $this->naOrder = $this->naOrder === null ? $aArr : array_merge($this->naOrder, $aArr);
        return $this;
    }

    /**
     * @return array|null
     */
    public function getOrderBy()
    {
        return $this->naOrder;
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
     * @return int|null
     */
    public function getLimit()
    {
        return $this->niLimit;
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

    /**
     * @return int|null
     */
    public function getOffset()
    {
        return $this->niOffset;
    }

    /**
     * @param string $sAlias
     *
     * @return $this
     */
    public function alias($sAlias)
    {
        $this->nsAlias = $sAlias;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getAlias()
    {
        return $this->nsAlias;
    }

    abstract public function __toString();

    protected function parseCondition($aWhere)
    {
        # 为空直接返回1
        if (empty($aWhere)) {
            return '1';
        }

        $aWhereBuild = array();

        # 提取出条件关系
        if (isset($aWhere[-1])) {
            $sRel = strtoupper($aWhere[-1]);
            unset($aWhere[-1]);
        } else {
            $sRel = 'AND';
        }

        # 遍历条件
        foreach ($aWhere as $mK => $mV) {
            if (is_int($mK)) {
                if (is_object($mV)) {
                    # 如果是复杂表达式
                    $aWhereBuild[] = (string)$mV;
                    continue;
                } else {
                    # 如果是子条件, 递归调用
                    $aWhereBuild[] = '(' . self::parseCondition($mV) . ')';
                    continue;
                }
            }

            # 如果不是子条件

            # 直接解析
            list($sKey, $sOP) = array_replace(array('', '='), explode(' ', $mK, 2));
            $sOP = strtoupper(trim($sOP));

            # Common
            if ($sOP !== 'IN' && $sOP !== 'NOT IN') {
                $aWhereBuild[] = "`$sKey` $sOP " . (is_string($mV) ? "'$mV'" : (string)$mV);
                continue;
            }

            # IN / NOT IN (mV is an array)
            if (empty($mV) || !is_array($mV)) {
                $aWhereBuild[] = '1';
                continue;
            }
            $aV = array();
            foreach ($mV as $mVV) {
                $mFixV = is_string($mVV) ? "'$mVV'" : (string)$mVV;

                if (!isset($aV[$mFixV])) {
                    $aV[$mFixV] = $mFixV;
                }
            }
            $aWhereBuild[] = "$sKey $sOP (" . implode(',', $aV) . ')';
        }

        # 返回结果
        return implode(" $sRel ", $aWhereBuild);
    }

    protected function parseJoin()
    {
        if ($this->naJoin === null) {
            return null;
        }

        $aArr = array();
        foreach ($this->naJoin as $aRow) {
            $aArr[] = sprintf("%s JOIN %s ON %s", $aRow[2], $aRow[0], $this->parseCondition($aRow[1]));
        }

        return implode(' ', $aArr);
    }

    protected function parseOrder()
    {
        if ($this->naOrder === null) {
            return null;
        }

        $aArr = array();
        foreach ($this->naOrder as $sV) {
            $sV = ltrim($sV);
            switch ($sV[0]) {
                case '-':
                    $aArr[] = substr($sV, 1) . ' DESC';
                    break;
                case '+':
                    $aArr[] = substr($sV, 1) . ' ASC';
                    break;
                default:
                    $aArr[] = $sV . ' ASC';
                    break;
            }
        }

        return implode(',', $aArr);
    }

    protected function parseTable()
    {
        return is_string($this->sTable_SQLSEL) ? "`{$this->sTable_SQLSEL}`" :
            (
            $this->sTable_SQLSEL->nsAlias === null ?
                (string)$this->sTable_SQLSEL :
                '(' . (string)$this->sTable_SQLSEL . ') AS `' . $this->nsAlias . '`'
            );
    }
}