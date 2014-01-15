<?php
namespace Slime\Component\Helper;

/**
 * Class Packer
 *
 * @package Slime\Component\Helper
 * @author  smallslime@gmail.com
 */
class Packer
{
    const BEFORE = 0;
    const AFTER  = 1;

    protected $aAOPCallBack = array();
    protected $Obj          = null;

    /**
     * @param object $mObj         obj to be packed
     * @param array  $aAOPCallBack ['execute.before,query.after' => [function(){xxx}, 'cbFunc1'], ...]
     */
    public function __construct($mObj, array $aAOPCallBack = array())
    {
        $this->Obj = $mObj;
        if (!empty($aAOPCallBack)) {
            foreach ($aAOPCallBack as $sExplain => $aCB) {
                foreach ($aCB as $mCB) {
                    $this->addCB($sExplain, $mCB);
                }
            }
        }
    }

    /**
     * @param string $sExplain 'execute.before,query.after'
     * @param mixed  $mCB
     *
     * @return $this
     */
    public function addCB($sExplain, $mCB)
    {
        $aArr = explode(',', $sExplain);
        foreach ($aArr as $sPoint) {
            list($sMethod, $sPosition) = array_replace(array('', 'both'), explode('.', $sPoint, 2));
            if ($sPosition === 'both') {
                $this->aAOPCallBack[$sMethod][self::BEFORE][] = $mCB;
                $this->aAOPCallBack[$sMethod][self::AFTER][]  = $mCB;
            } elseif ($sPosition === 'before') {
                $this->aAOPCallBack[$sMethod][self::BEFORE][] = $mCB;
            } else {
                $this->aAOPCallBack[$sMethod][self::AFTER][] = $mCB;
            }
        }

        return $this;
    }

    public function __get($sKey)
    {
        return $this->Obj->$sKey;
    }

    public function __set($sKey, $mValue)
    {
        $this->Obj->sKey = $mValue;
    }

    public function __call($sMethod, $aArgv)
    {
        if (empty($this->aAOPCallBack[$sMethod])) {
            return empty($aArgv) ? $this->Obj->$sMethod() : call_user_func_array(array($this->Obj, $sMethod), $aArgv);
        }

        $aCB    = $this->aAOPCallBack[$sMethod];
        $Result = new \ArrayObject(array('value' => null));

        if (!empty($aCB[self::BEFORE])) {
            foreach ($aCB[self::BEFORE] as $mCB) {
                if (call_user_func($mCB, $this->Obj, $sMethod, $aArgv, $Result) === false) {
                    break;
                }
            }
        }

        if ($Result['value'] === null) {
            $Result['value'] = call_user_func_array(array($this->Obj, $sMethod), $aArgv);
        }

        if (!empty($aCB[self::AFTER])) {
            foreach ($aCB[self::AFTER] as $mCB) {
                if (call_user_func($mCB, $this->Obj, $sMethod, $aArgv, $Result) === false) {
                    break;
                }
            }
        }

        return $Result['value'];
    }
}