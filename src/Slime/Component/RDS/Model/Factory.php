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
    public $bAutoCreate = true;
    public $bCompatibleMode = false;

    /** @var Model[] */
    protected $aModel = array();

    protected $sDefaultModelClass;

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
        throw new \BadMethodCallException("Call $sModel error");
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
                $this->bAutoCreate &&
                (!isset($this->aModelConf[$sModelName]) || !isset($this->aModelConf[$sModelName]['db']))
            ) {
                $this->aModelConf[$sModelName]['db'] = 'default';
            }

            $aConf = $this->aModelConf[$sModelName];
            $sDB   = $aConf['db'];
            if (!isset($this->aCURD[$sDB])) {
                throw new \OutOfRangeException("There is no database config [$sDB] exist");
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
}