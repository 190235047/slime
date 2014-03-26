<?php
namespace Slime\Component\RDS\Model;

use Slime\Component\RDS\CURD;

/**
 * Class Factory
 *
 * @package Slime\Component\RDS\Model
 * @author  smallslime@gmail.com
 */
class Factory
{
    /** @var string | null  null means disable auto_create function */
    public $sDefaultDB = 'default';

    protected $aCURD              = array();
    protected $bCompatibleMode    = false;
    protected $bTmpCompatibleMode = null;

    /** @var Model[] */
    protected $aModel = array();

    protected $sDefaultModelClass;

    /**
     * @param array  $aDBConfigAll
     * @param array  $aModelConfig
     * @param string $sAppModelNS
     * @param string $sDefaultModelClass
     * @param array  $aAOP
     */
    public function __construct(
        $aDBConfigAll,
        $aModelConfig,
        $sAppModelNS = '',
        $sDefaultModelClass = 'Slime\\Component\\RDS\\Model\\Model',
        array $aAOP = array()
    ) {
        foreach ($aDBConfigAll as $sK => $aDBConfig) {
            $this->aCURD[$sK] = new CURD(
                $sK,
                $aDBConfig['dsn'],
                $aDBConfig['username'],
                $aDBConfig['password'],
                $aDBConfig['options'],
                $aAOP
            );
        }
        $this->aModelConf         = $aModelConfig;
        $this->sAppModelNS        = $sAppModelNS;
        $this->sDefaultModelClass = $sDefaultModelClass;
    }

    public function __call($sModel, $aArg = null)
    {
        if (($sModel = substr($sModel, 3)) !== false) {
            return $this->get($sModel);
        }
        throw new \BadMethodCallException("[MODEL] : Call $sModel error");
    }

    /**
     * @param string $sModelName
     *
     * @return Model
     * @throws \OutOfRangeException
     */
    public function get($sModelName)
    {
        if (!isset($this->aModel[$sModelName])) {
            if (
                $this->sDefaultDB !== null &&
                (!isset($this->aModelConf[$sModelName]) || !isset($this->aModelConf[$sModelName]['db']))
            ) {
                $this->aModelConf[$sModelName]['db'] = $this->sDefaultDB;
            }

            $aConf = $this->aModelConf[$sModelName];
            $sDB   = $aConf['db'];
            if (!isset($this->aCURD[$sDB])) {
                throw new \OutOfRangeException("[MODEL] : There is no database config [$sDB] exist");
            }

            $sModelClassName = isset($aConf['model_class']) ?
                $this->sAppModelNS . '\\' . $aConf['model_class'] :
                $this->sDefaultModelClass;

            $this->aModel[$sModelName] = new $sModelClassName(
                $sModelName,
                $this->aCURD[$sDB],
                $aConf,
                $this
            );
        }

        return $this->aModel[$sModelName];
    }

    /**
     * @param bool $bCompatibleMode
     *
     * @return void
     */
    public function setTmpCompatibleMode($bCompatibleMode = true)
    {
        if ($bCompatibleMode !== $this->bCompatibleMode) {
            $this->bTmpCompatibleMode = $this->bCompatibleMode;
            $this->bCompatibleMode    = $bCompatibleMode;
        }
    }

    /**
     * @return void
     */
    public function resetCompatibleMode()
    {
        if ($this->bTmpCompatibleMode !== null) {
            $this->bCompatibleMode    = $this->bTmpCompatibleMode;
            $this->bTmpCompatibleMode = null;
        }
    }

    /**
     * @return bool
     */
    public function isCompatibleMode()
    {
        return (bool)$this->bCompatibleMode;
    }

    /**
     * @param bool $b
     * @return void
     */
    public function setCompatibleMode($b = true)
    {
        $this->bCompatibleMode = $b;
    }

    /**
     * @param Item | CompatibleItem | Group | null $mData
     * @return bool
     */
    public static function isModelDataEmpty($mData)
    {
        if ($mData===null ||
            $mData instanceof CompatibleItem ||
            ($mData instanceof Group && $mData->count() == 0)
        ) {
            return true;
        } else {
            return false;
        }
    }

    public function __get($sVar)
    {
        return $this->$sVar;
    }
}