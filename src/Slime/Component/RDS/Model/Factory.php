<?php
namespace Slime\Component\RDS\Model;

use Slime\Component\RDS\CURD;
use Slime\Component\Context\Context;

/**
 * Class Factory
 *
 * @package Slime\Component\RDS\Model
 * @author  smallslime@gmail.com
 */
class Factory
{
    public $bAutoCreate = true;

    /** @var Model[] */
    protected $aModel = array();

    /**
     * @var Context|null
     */
    public $Context;

    public function __construct($aDBConfigAll, $aModelConfig, $sAppModelNS = '')
    {
        foreach ($aDBConfigAll as $sK => $aDBConfig) {
            $this->aCURD[$sK] = new CURD(
                $sK,
                $aDBConfig['dsn'],
                $aDBConfig['username'],
                $aDBConfig['password'],
                $aDBConfig['options']
            );
        }
        $this->aModelConf  = $aModelConfig;
        $this->sAppModelNS = rtrim($sAppModelNS, '\\');
        $this->Context     = class_exists('Slime\Component\Context\Context') ? Context::getInst() : null;
    }

    public function __call($sModel, $aArg = null)
    {
        if (($sModel = substr($sModel, 3)) !== false) {
            return $this->get($sModel);
        }
        throw new \Exception("Call $sModel error");
    }

    /**
     * @param string $sModelName
     *
     * @return Model
     * @throws \Exception
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
                throw new \Exception("There is no database config [$sDB] exist");
            }

            $sModelClassName = isset($aConf['model_class']) ?
                $this->sAppModelNS . '\\' . $aConf['model_class'] :
                '\\Slime\\Component\\RDS\\Model\\Model';

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