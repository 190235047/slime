<?php
namespace Slime\Component\RDBMS\DAL;

use Slime\Component\Context\Packer;

/**
 * Class DAL
 *
 * @package Slime\Component\RDBMS
 * @author  smallslime@gmail.com
 */
class Engine
{
    /** @var \PDO[] */
    protected $aInstance;
    protected $mCBJudge;

    public function __construct(array $aParams, $mCBJudge = null)
    {
        foreach ($aParams as $sK => $aParam) {
            $this->aInstance[$sK] = $aParam;
        }

        $this->mCBJudge = $mCBJudge === null ? array() : $mCBJudge;
    }

    /**
     * @param SQL | string | null $nsoSQL
     * @param mixed               $mCBJudge
     *
     * @return \PDO
     *
     * @throws \OutOfBoundsException
     */
    public function getInstance($nsoSQL = null, $mCBJudge = null)
    {
        $sK = call_user_func($mCBJudge === null ? $this->mCBJudge : $mCBJudge, $nsoSQL, $this->aInstance);
        if (empty($this->aInstance[$sK])) {
            throw new \OutOfBoundsException("There is not config[$sK] in aParams");
        }
        $aQ = &$this->aInstance[$sK];
        if (empty($aQ['__instance__'])) {
            $aQ['__instance__'] = new Packer(
                new \PDO($aQ['dsn'], $aQ['username'], $aQ['passwd'], $aQ['option'])
            );
        }

        END:
        return $aQ['__instance__'];
    }

    /**
     * @param SQL $SQL
     *
     * @return bool | array : return false on query failed
     */
    public function Q($SQL)
    {
        if (count($SQL->getBind()) === 0) {
            $STMT = $this->query($SQL);
            return $STMT===false ? false : $STMT->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            $STMT = $this->prepare($SQL);
            if ($STMT===false) {
                return false;
            }
            self::bind($STMT, $SQL->getBind());
            return $STMT->fetchAll(\PDO::FETCH_ASSOC);
        }
    }

    /**
     * @param SQL $SQL
     *
     * @return bool
     */
    public function E($SQL)
    {
        if (count($SQL->getBind()) === 0) {
            return $this->exec($SQL);
        } else {
            $STMT = $this->prepare($SQL);
            if ($STMT===false) {
                return false;
            }
            return $STMT->execute();
        }
    }

    /**
     * @param string | SQL $soSQL
     *
     * @return \PDOStatement
     */
    public function prepare($soSQL)
    {
        return $this->getInstance($soSQL)->prepare((string)$soSQL);
    }

    /**
     * @param string | SQL $soSQL
     *
     * @return \PDOStatement
     */
    public function query($soSQL)
    {
        return $this->getInstance($soSQL)->query((string)$soSQL);
    }

    /**
     * @param string | SQL $soSQL
     *
     * @return int
     */
    public function exec($soSQL)
    {
        return $this->getInstance($soSQL)->exec((string)$soSQL);
    }

    /**
     * @param \PDOStatement $STMT
     * @param Bind          $Bind
     */
    public static function bind($STMT, $Bind)
    {
        $aData = $Bind->getData();
        foreach ($aData as $aRow) {
            $STMT->bindValue((string)$aRow[0], $aRow[1], $aRow[2]);
        }
    }
}
