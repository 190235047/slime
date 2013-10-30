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

    /** @var Model[] */
    protected $aModel = array();

    public function __construct($aDBConfigAll, $aModelConfig)
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
        $this->aModelConf = $aModelConfig;
    }

    /**
     * @param string $sModel
     *
     * @return Model
     * @throws \Exception
     */
    public function get($sModel)
    {
        if (!isset($this->aModel[$sModel])) {
            if (
                $this->bAutoCreate &&
                (!isset($this->aModelConf[$sModel]) || !isset($this->aModelConf[$sModel]['db']))
            ) {
                $this->aModelConf[$sModel]['db'] = 'default';
            }
            $sDB = $this->aModelConf[$sModel]['db'];
            if (!isset($this->aCURD[$sDB])) {
                throw new \Exception("here is no database config [$sDB] exist");
            }
            $this->aModel[$sModel] = new Model(
                $sModel,
                $this->aCURD[$sDB],
                $this->aModelConf[$sModel],
                $this
            );
        }

        return $this->aModel[$sModel];
    }
}