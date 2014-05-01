<?php
namespace Slime\Component\RDS;

use Slime\Component\Helper\Packer;

/**
 * Class CURD
 *
 * @package Slime\Component\RDS
 * @author  smallslime@gmail.com
 *
 * @property-read string $sDSN
 * @property-read string $sUsername
 * @property-read string $sPassword
 * @property-read array  $aOptions
 */
class CURD
{
    const INSERT_STANDARD = 0;
    const INSERT_IGNORE   = 1;
    const INSERT_REPLACE  = 2;

    /** @var \PDO */
    private $Instance;

    public $sDSN;
    public $sUsername;
    public $sPassword;
    public $aOptions = array();

    public $bCheckConnect = false;

    public function __construct(
        $sKey,
        $sDSN,
        $sUsername,
        $sPassword,
        array $aOptions = array(),
        array $aAOPCallBack = array()
    ) {
        $this->sKey         = $sKey;
        $this->sDSN         = $sDSN;
        $this->sUsername    = $sUsername;
        $this->sPassword    = $sPassword;
        $this->aOptions     = $aOptions;
        $this->aAOPCallBack = $aAOPCallBack;
    }

    /**
     * @param bool $bCheckConnect
     *
     * @return \PDO
     */
    public function getInstance($bCheckConnect = false)
    {
        if (!$this->Instance ||
            (
                ($bCheckConnect || $this->bCheckConnect) &&
                (!$this->Instance->getAttribute(\PDO::ATTR_SERVER_INFO))
            )
        ) {
            $this->Instance = new Packer(
                new \PDO($this->sDSN, $this->sUsername, $this->sPassword, $this->aOptions),
                $this->aAOPCallBack
            );
        }
        return $this->Instance;
    }

    /**
     * @param string $sTable
     * @param array  $aWhere
     * @param string $sAttr
     * @param string $sSelect
     * @param bool   $bOnlyOne
     * @param int    $iFetchStyle
     * @param mixed  $mFetchArgs
     *
     * @return array|mixed
     */
    public function querySmarty(
        $sTable,
        array $aWhere = null,
        $sAttr = '',
        $sSelect = '',
        $bOnlyOne = false,
        $iFetchStyle = \PDO::FETCH_ASSOC,
        $mFetchArgs = null
    ) {
        if (empty($sSelect)) {
            $sSelect = '*';
        }
        if ($bOnlyOne && stripos($sAttr, 'limit') === false) {
            $sAttr .= " LIMIT 1";
        }
        $aArgs       = array();
        $sWhere      = self::buildCondition($aWhere, $aArgs);
        $sSQLPrepare = "SELECT $sSelect FROM $sTable WHERE $sWhere $sAttr";
        $Stmt        = $this->getInstance()->prepare($sSQLPrepare);
        $Stmt->execute($aArgs);
        if ($mFetchArgs !== null) {
            $Stmt->setFetchMode($iFetchStyle, $mFetchArgs);
        } else {
            $Stmt->setFetchMode($iFetchStyle);
        }
        return $bOnlyOne ? $Stmt->fetch() : $Stmt->fetchAll();
    }

    /**
     * @param string $sTable
     * @param array  $aWhere
     * @param string $sAttr
     *
     * @return int
     */
    public function queryCount(
        $sTable,
        $aWhere = array(),
        $sAttr = ''
    ) {
        $aArgs       = array();
        $sWhere      = self::buildCondition($aWhere, $aArgs);
        $sSQLPrepare = "SELECT count(1) as total FROM $sTable WHERE $sWhere $sAttr";
        $Stmt        = $this->getInstance()->prepare($sSQLPrepare);
        $Stmt->execute($aArgs);
        $mResult = $Stmt->fetch();
        return isset($mResult['total']) ? (int)$mResult['total'] : 0;
    }

    /**
     * @param string $sTable
     * @param array  $aKVMap
     * @param array  $aWhere
     *
     * @return bool
     */
    public function updateSmarty($sTable, array $aKVMap, array $aWhere)
    {
        $aUpdatePre = $aUpdateData = array();
        foreach ($aKVMap as $sK => $sV) {
            if (is_int($sK)) {
                //['a'=>'a1', 'b=b+1']
                $aUpdatePre[] = $sV;
            } else {
                $sKK           = self::addQuote($sK);
                $aUpdatePre[]  = "$sKK = ?";
                $aUpdateData[] = $sV;
            }
        }
        $aArgs       = array();
        $sSQLPrepare = sprintf(
            "UPDATE %s SET %s WHERE %s",
            $sTable,
            implode(',', $aUpdatePre),
            self::buildCondition($aWhere, $aArgs)
        );
        $aData       = array_merge($aUpdateData, $aArgs);
        $STMT        = $this->getInstance()->prepare($sSQLPrepare);
        return $STMT->execute($aData);
    }

    /**
     * @param string $sTable
     * @param array  $aWhere
     *
     * @return bool
     */
    public function deleteSmarty($sTable, array $aWhere)
    {
        $aArgs       = array();
        $sSQLPrepare = sprintf("DELETE FROM %s WHERE %s", $sTable, self::buildCondition($aWhere, $aArgs));
        $STMT        = $this->getInstance()->prepare($sSQLPrepare);
        return $STMT->execute($aArgs);
    }

    /**
     * @param string $sTable
     * @param array  $aKVMap
     * @param int    $iType
     *
     * @return null|string
     */
    public function insertSmarty($sTable, array $aKVMap, $iType = self::INSERT_STANDARD)
    {
        $aFixKey     = self::addQuote(array_keys($aKVMap));
        $sSQLPrepare = sprintf(
            "%s INTO %s %s VALUES(%s)",
            $iType == self::INSERT_IGNORE ? 'INSERT IGNORE' : ($iType == self::INSERT_REPLACE ? 'REPLACE' : 'INSERT'),
            $sTable,
            isset($aKVMap[0]) ? '' : '(' . implode(',', $aFixKey) . ')',
            implode(',', array_pad(array(), count($aKVMap), '?'))
        );
        $PDO         = $this->getInstance();
        $STMT        = $PDO->prepare($sSQLPrepare);
        if ($STMT->execute(array_values($aKVMap))) {
            return $PDO->lastInsertId();
        } else {
            return null;
        }
    }

    /**
     * @param string $sTable
     * @param array  $aKVMap
     * @param array  $aUpdateKey
     *
     * @return bool
     */
    public function insertUpdateSmarty($sTable, array $aKVMap, $aUpdateKey)
    {
        $aUpdatePre = $aUpdateData = array();
        foreach ($aUpdateKey as $sK) {
            $sKK           = self::addQuote($sK);
            $aUpdatePre[]  = "$sKK = ?";
            $aUpdateData[] = $aKVMap[$sK];
        }
        $sSQLPrepare = sprintf(
            "INSERT INTO %s %s VALUES(%s) ON DUPLICATE KEY UPDATE %s",
            $sTable,
            '(' . implode(',', array_keys($aKVMap)) . ')',
            implode(',', array_pad(array(), count($aKVMap), '?')),
            implode(' , ', $aUpdatePre)
        );
        $PDO         = $this->getInstance();
        $STMT        = $PDO->prepare($sSQLPrepare);
        $aData       = array_merge(array_values($aKVMap), $aUpdateData);
        return $STMT->execute($aData);
    }

    /**
     * @example   : [-1:OR, 'username like':'%abc%', 'last_login >':'2013', ['rank >' : 3, 'vip' : 1]]
     *            result: username like ? OR last_login = ? OR (rank > ? AND vip = ?)
     *            $aArgs: array('%abc%', '2013', 3, 1)
     *
     * @param array $aWhere
     * @param array $aArgs
     *
     * @return int|string
     */
    public static function buildCondition(array $aWhere = null, &$aArgs = array())
    {
        # 为空直接返回1
        if (empty($aWhere)) {
            return 1;
        }

        # 提取出条件关系
        $aWhereBuild = array();
        if (isset($aWhere[-1])) {
            $sRelation = strtoupper($aWhere[-1]);
            unset($aWhere[-1]);
        } else {
            $sRelation = 'AND';
        }

        # 遍历条件
        foreach ($aWhere as $sK => $mV) {
            if (is_int($sK)) {
                # 如果是子条件, 递归调用
                $aWhereBuild[] = '(' . self::buildCondition($mV, $aArgs) . ')';
            } else {
                # 如果不是子条件, 解析
                list($sKey, $sOP) = array_replace(array('', '='), explode(' ', $sK, 2));
                $sKey = self::addQuote($sKey);
                $sOP  = trim(strtoupper($sOP));
                if ($sOP == 'IN' || $sOP == 'NOT IN') {
                    if (empty($mV)) {
                        $aWhereBuild[] = '1';
                    } else {
                        $aWhereBuild[] = sprintf(
                            "$sKey $sOP (%s)",
                            implode(',', array_fill(0, count($mV), '?'))
                        );
                        $aArgs         = array_merge($aArgs, $mV);
                    }
                } else {
                    $aWhereBuild[] = "$sKey $sOP ?";
                    $aArgs[]       = $mV;
                }
            }
        }

        # 返回结果
        return implode(" $sRelation ", $aWhereBuild);
    }


    public static function addQuote($mK)
    {
        if (is_array($mK)) {
            $aKFix = array();
            foreach ($mK as $mKK => $mVV) {
                $aKFix[$mKK] = self::addQuote($mVV);
            }
            return $aKFix;
        } else {
            $aK = explode('.', $mK);
            return count($aK) === 1 ? self::_addQuote($aK[0]) :
                implode('.', array_map(array('Slime\\Component\\RDS\\CURD', '_addQuote'), $aK));
        }
    }

    public static function _addQuote($sK)
    {
        return $sK[0] === ':' ? substr($sK, 1) : "`$sK`";
    }
}
