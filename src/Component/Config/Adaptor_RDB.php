<?php
namespace Slime\Component\Config;

use Slime\Component\Context\Arr;

/**
 * Class Configure
 *
 * @package Slime\Component\Config
 * @author  smallslime@gmail.com
 */
class Adaptor_RDB extends Adaptor_ABS
{
    protected $sTable;
    protected $sFieldK;
    protected $sFieldV;

    /** @var array */
    protected $aData = null;

    /**
     * @param \Slime\Component\RDS\CURD $PDO
     * @param string                    $sTable
     * @param string                    $sFieldKey
     * @param string                    $sFieldValue
     */
    public function __construct($PDO, $sTable, $sFieldKey = 'key', $sFieldValue = 'value')
    {
        $this->PDO     = $PDO;
        $this->sTable  = $sTable;
        $this->sFieldK = $sFieldKey;
        $this->sFieldV = $sFieldValue;
    }

    /**
     * @param string $sKey
     * @param mixed  $mDefaultValue
     * @param bool   $bForce
     *
     * @throws \OutOfRangeException
     * @return mixed
     */
    public function get($sKey, $mDefaultValue = null, $bForce = false)
    {
        if ($this->aData === null) {
            $aArr        = $this->PDO->querySmarty($this->sTable);
            $this->aData = empty($aArr) ? array() : Arr::changeIndexToKVMap($aArr, $this->sFieldK, $this->sFieldV);
        }
        if (!isset($this->aData[$sKey])) {
            if ($bForce) {
                throw new \OutOfRangeException("[CONFIG] : Key[$sKey] is not exist");
            } else {
                $mRS = $mDefaultValue;
                goto RET;
            }
        }
        $mRS = $this->bParseMode ? Configure::parseRecursion($this->aData[$sKey], $this) : $this->aData[$sKey];

        RET:
            return $mRS;
    }
}