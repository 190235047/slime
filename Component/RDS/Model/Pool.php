<?php
namespace SlimeFramework\Component\RDS;

use Psr\Log\LoggerInterface;
use SlimeFramework\Component\RDS\CURD;
use SlimeFramework\Component\RDS\Model_Model;

class Model_Pool
{
    /** @var CURD[] */
    protected $aCURD;

    /** @var Model_Model[] */
    protected $aModel;

    protected $aModelConf;

    public function __construct($aDBConfigAll, $aModelConfig, LoggerInterface $Log)
    {
        foreach ($aDBConfigAll as $sK => $aDBConfig) {
            $this->aCURD[$sK] = new CURD(
                $aDBConfig['dsn'],
                $aDBConfig['username'],
                $aDBConfig['password'],
                $aDBConfig['options']
            );
        }
        $this->aModelConf = $aModelConfig;
        $this->Log        = $Log;
    }

    /**
     * @param $sModel
     * @return Model_Model
     */
    public function get($sModel)
    {
        if (!isset($this->aModel[$sModel])) {
            $sDB = $this->aModelConf[$sModel]['db'];
            if (!isset($this->aCURD[$sDB])) {
                $this->Log->error('there is no database config [{dbkey}] exist', array('dbkey' => $sDB));
                exit(1);
            }
            $this->aModel[$sModel] = new Model_Model(
                $sModel,
                $this->aCURD[$sDB],
                $this->aModelConf[$sModel],
                $this,
                $this->Log
            );
        }

        return $this->aModel[$sModel];
    }
}