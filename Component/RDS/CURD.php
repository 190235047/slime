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
        $aArgs = array();
        $sWhere      = $this->buildCondition($aWhere, $aArgs);
        $sSQLPrepare = "SELECT $sSelect FROM $sTable WHERE $sWhere $sAttr";
        $Stmt        = $this->getInstance()->prepare($sSQLPrepare);
        $Stmt->execute($aArgs);
        if ($mFetchArgs!==null) {
            $Stmt->setFetchMode($iFetchStyle, $mFetchArgs);
        } else {
            $Stmt->setFetchMode($iFetchStyle);
        }
        return $bOnlyOne ? $Stmt->fetch() : $Stmt->fetchAll();
    }
 
    public function queryCount(
        $sTable,
        $aWhere = array(),
        $sAttr = '',
        $sSelect = '1'
    ) {
        $aArgs = array();
        $sWhere      = $this->buildCondition($aWhere, $aArgs);
        $sSQLPrepare = "SELECT count($sSelect) as total FROM $sTable WHERE $sWhere $sAttr";
        $Stmt        = $this->getInstance()->prepare($sSQLPrepare);
        $Stmt->execute($aArgs);
        $mResult = $Stmt->fetch();
        return isset($mResult['total']) ? (int)$mResult['total'] : 0;
    }

    /**
     * @param string $sTable
     * @param array $aKVMap
     * @param array $aWhere
     * @return bool
     */
    public function updateSmarty($sTable, array $aKVMap, array $aWhere)
    {
        $aUpdatePre = $aUpdateData = array();
        foreach ($aKVMap as $sK => $sV) {
            $aUpdatePre[]  = "`$sK` = ?";
            $aUpdateData[] = $sV;
        }
        $aArgs = array();
        $sSQLPrepare = sprintf(
            "UPDATE %s SET %s WHERE %s",
            $sTable,
            implode(' , ', $aUpdatePre),
            $this->buildCondition($aWhere, $aArgs)
        );
        $aData       = array_merge($aUpdateData, $aArgs);
        $STMT        = $this->getInstance()->prepare($sSQLPrepare);
        return $STMT->execute($aData);
    }

    public function deleteSmarty($sTable, array $aWhere)
    {
        $aArgs = array();
        $sSQLPrepare = sprintf("DELETE FROM %s WHERE %s", $sTable, $this->buildCondition($aWhere, $aArgs));
        $STMT        = $this->getInstance()->prepare($sSQLPrepare);
        return $STMT->execute($aArgs);
    }

    /**
     * @param string $sTable
     * @param array $aKVMap
     * @param int $iType
     * @return null|string
     */
    public function insertSmarty($sTable, array $aKVMap, $iType = self::INSERT_STANDARD)
    {
        $sSQLPrepare = sprintf(
            "%s INTO %s %s VALUES(%s)",
            $iType == self::INSERT_IGNORE ? 'INSERT IGNORE' : ($iType == self::INSERT_REPLACE ? 'REPLACE' : 'INSERT'),
            $sTable,
            isset($aKVMap[0]) ? '' : '(`' . implode('`,`', array_keys($aKVMap)) . '`)',
            implode(',', array_pad(array(), count($aKVMap), '?'))
        );
        $PDO  = $this->getInstance();
        $STMT = $PDO->prepare($sSQLPrepare);
        if ($STMT->execute(array_values($aKVMap))) {
            return $PDO->lastInsertId();
        } else {
            return null;
        }
    }

    public function buildCondition($aWhere, &$aArgs = array())
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
                $aWhereBuild[] = '(' . $this->buildCondition($mV, $aArgs) . ')';
            } else {
                $aTmp          = explode(' ', $sK, 2);
                $sKey          = $aTmp[0];
                $sOpt          = isset($aTmp[1]) ? trim($aTmp[1]) : '=';
                # hack
                $inOp = strtoupper($sOpt);
                if ($inOp=='IN' || $inOp=='NOT IN') {
                    $aWhereBuild[] = sprintf("`$sKey` IN (%s)", implode(',', array_fill(0, count($mV), '?')));
                    $aArgs = array_merge($aArgs, $mV);
                } else {
                    $aWhereBuild[] = "`$sKey` $sOpt ?";
                    $aArgs[] = $mV;
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