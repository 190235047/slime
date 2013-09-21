<?php
namespace SlimeFramework\Component\RDS;

/**
 * Class PDO
 * @package Slime\RDS
 * @author smallslime@gmail.com
 * @version 0.1
 */
class CURD
{
    const INSERT_STANDARD = 0;
    const INSERT_IGNORE   = 1;
    const INSERT_REPLACE  = 2;

    /** @var \PDO */
    private $Instance;

    private $sDSN;
    private $sUsername;
    private $sPassword;
    private $aOptions = array();

    public $bCheckConnect = false;

    public function __construct($sDSN, $sUsername, $sPassword, $aOptions = array())
    {
        $this->sDSN      = $sDSN;
        $this->sUsername = $sUsername;
        $this->sPassword = $sPassword;
        $this->aOptions  = $aOptions;
    }

    /**
     * @param bool $bCheckConnect
     * @return \PDO
     */
    public function getInstance($bCheckConnect = false)
    {
        if (!$this->Instance) {
            $this->Instance = new \PDO($this->sDSN, $this->sUsername, $this->sPassword, $this->aOptions);
        } else {
            if ($bCheckConnect || $this->bCheckConnect) {
                if (!$this->Instance || $this->Instance->getAttribute(
                        \PDO::ATTR_SERVER_INFO
                    ) == 'MySQL server has gone away'
                ) {
                    $this->Instance = new \PDO($this->sDSN, $this->sUsername, $this->sPassword, $this->aOptions);
                }
            }
        }
        return $this->Instance;
    }

    public function closeInstance()
    {
        $this->Instance = null;
    }

    public function querySmarty(
        $sTable,
        $aWhere = array(),
        $sAttr = '',
        $sSelect = '',
        $bOnlyOne = false,
        $iFetchStyle = \PDO::FETCH_ASSOC,
        $mFetchArgs = null
    ) {
        if (empty($sSelect)) {
            $sSelect = '*';
        }
        if ($bOnlyOne && stripos($sAttr, 'limit')===false) {
            $sAttr .= " LIMIT 1";
        }

        $sWhere      = $this->buildCondition($aWhere);
        $sSQLPrepare = "SELECT $sSelect FROM $sTable WHERE $sWhere $sAttr";
        $Stmt        = $this->getInstance()->prepare($sSQLPrepare);
        $aExec       = array();
        foreach ($aWhere as $mV) {
            if (is_array($mV)) {
                $aExec = array_merge($aExec, $mV);
            } else {
                $aExec[] = $mV;
            }
        }
        $Stmt->execute($aExec);
        return $bOnlyOne ? $Stmt->fetch($iFetchStyle, $mFetchArgs) : ($mFetchArgs===null ? $Stmt->fetchAll($iFetchStyle) : $Stmt->fetchAll($iFetchStyle, $mFetchArgs));
    }

    public function queryCount(
        $sTable,
        $aWhere = array(),
        $sAttr = '',
        $sSelect = '1'
    ) {
        $sWhere      = $this->buildCondition($aWhere);
        $sSQLPrepare = "SELECT count($sSelect) as total FROM $sTable WHERE $sWhere $sAttr";
        $Stmt        = $this->getInstance()->prepare($sSQLPrepare);
        $Stmt->execute(array_values($aWhere));
        $mResult = $Stmt->fetch();
        return isset($mResult['total']) ? (int)$mResult['total'] : 0;
    }

    public function updateSmarty($sTable, array $aKVMap, array $aWhere)
    {
        $aUpdatePre = $aUpdateData = array();
        foreach ($aKVMap as $sK => $sV) {
            $aUpdatePre[]  = "`$sK` = ?";
            $aUpdateData[] = $sV;
        }
        $sSQLPrepare = sprintf(
            "UPDATE %s SET %s WHERE %s",
            $sTable,
            implode(' , ', $aUpdatePre),
            $this->buildCondition($aWhere)
        );
        $aData       = array_merge($aUpdateData, array_values($aWhere));
        $STMT        = $this->getInstance()->prepare($sSQLPrepare);
        return $STMT->execute($aData);
    }

    public function deleteSmarty($sTable, array $aWhere)
    {
        $sSQLPrepare = sprintf("DELETE FROM %s WHERE %s", $sTable, $this->buildCondition($aWhere));
        $STMT        = $this->getInstance()->prepare($sSQLPrepare);
        return $STMT->execute(array_values($aWhere));
    }

    public function insertSmarty($sTable, array $aKVMap, $iType = self::INSERT_STANDARD)
    {
        $sSQLPrepare = sprintf(
            "%s INTO %s %s VALUES(%s)",
            $iType == self::INSERT_IGNORE ? 'INSERT IGNORE' : ($iType == self::INSERT_REPLACE ? 'REPLACE' : 'INSERT'),
            $sTable,
            isset($aKVMap[0]) ? '' : '(`' . implode('`,`', array_keys($aKVMap)) . '`)',
            implode(',', array_pad(array(), count($aKVMap), '?'))
        );
        $STMT        = $this->getInstance()->prepare($sSQLPrepare);
        return $STMT->execute(array_values($aKVMap));
    }

    public function buildCondition($aWhere)
    {
        if (empty($aWhere)) {
            return 1;
        }
        $aWhereBuild = array();
        if (isset($aWhere[-1])) {
            $sRelation = (string)$aWhere[-1];
            unset($aWhere[-1]);
        } else {
            $sRelation = 'AND';
        }
        foreach ($aWhere as $sK => $mV) {
            if (is_int($sK)) {
                $aWhereBuild[] = '(' . $this->buildCondition($mV) . ')';
            } else {
                $aTmp          = explode(' ', $sK, 2);
                $sKey          = $aTmp[0];
                $sOpt          = isset($aTmp[1]) ? trim($aTmp[1]) : '=';
                # hack
                if (strtoupper($sOpt)=='IN') {
                    $aWhereBuild[] = sprintf("`$sKey` IN (%s)", implode(',', array_fill(0, count($mV), '?')));
                } else {
                    $aWhereBuild[] = "`$sKey` $sOpt ?";
                }
            }
        }
        return implode(" $sRelation ", $aWhereBuild);
    }

    public function __sleep()
    {
        return array('sDSN', 'sUsername', 'sPassword', 'aOptions', 'bCheckConnect');
    }
}

if (defined('SlimeFramework.RDS:AOP')) {
    AopPDO::register();
}