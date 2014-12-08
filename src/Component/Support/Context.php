<?php
namespace Slime\Component\Support;

/**
 * Class Context
 *
 * Context::$Inst is readOnly, please do not write
 *
 * @package Slime\Component\Support
 * @author  smallslime@gmail.com
 */
class Context
{
    /** @var Context */
    protected static $Inst;

    /**
     * @param mixed  $CFG
     * @param string $sCBDataKey
     *
     * @return Context
     */
    public static function create($CFG, $sCBDataKey)
    {
        self::$Inst = new static($CFG, $sCBDataKey);
        return self::$Inst;
    }

    /**
     * @return Context
     */
    public static function inst()
    {
        return self::$Inst;
    }

    protected $aData = array();
    protected $aDataConfig = array();
    protected $aCB = array();
    protected $CFG = null;

    /**
     * @param \Slime\Component\Config\IAdaptor $CFG
     * @param string                           $sCBDataKey
     */
    private function __construct($CFG, $sCBDataKey)
    {
        $this->CFG         = $CFG;
        $this->aDataConfig = $CFG->get($sCBDataKey, array());
        $CFG->setCTX($this);
    }

    public function __get($sName)
    {
        return $this->get($sName);
    }

    public function __call($sName, $aArgv)
    {
        return $this->call($sName, $aArgv);
    }

    /**
     * @param string $sName
     * @param bool   $bAttemptAutoBind
     *
     * @return bool
     */
    public function checkBound($sName, $bAttemptAutoBind = false)
    {
        if (isset($this->aData[$sName])) {
            return true;
        }

        if ($bAttemptAutoBind) {
            $this->aData[$sName] = $this->make($sName);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $sName
     *
     * @return bool
     */
    public function isCBBound($sName)
    {
        return isset($this->aCB[$sName]);
    }

    /**
     * @param string $sName
     *
     * @return object
     * @throws \OutOfBoundsException
     */
    public function make($sName)
    {
        if (!isset($this->aDataConfig[$sName])) {
            throw new \OutOfBoundsException("[CTX] ; [$sName] can not found in config");
        }
        $aArr = $this->aDataConfig[$sName];

        if (!empty($aArr['params']) && !empty($aArr['parse_params'])) {
            $aArr['params'] = $this->CFG->parse($aArr['params'], true);
        }
        if (isset($aArr['creator'])) {
            $Obj = call_user_func_array(
                array($aArr['class'], $aArr['creator']),
                empty($aArr['params']) ? array() : $aArr['params']
            );
        } else {
            if (empty($aArr['params'])) {
                $Obj = new $aArr['class']();
            } else {
                $Ref = new \ReflectionClass($aArr['class']);
                $Obj = $Ref->newInstanceArgs($aArr['params']);
            }
        }

        return isset($aArr['packer']) ? new Packer($Obj, (array)$aArr['packer']): $Obj;
    }

    /**
     * @param string $sName
     *
     * @return mixed
     */
    public function get($sName)
    {
        $this->checkBound($sName, true);
        return $this->aData[$sName];
    }

    /**
     * @param string $sName
     *
     * @return mixed
     */
    public function getIgnore($sName)
    {
        try {
            $this->checkBound($sName, true);
            return $this->aData[$sName];
        } catch (\OutOfBoundsException $E) {
            return null;
        }
    }

    /**
     * @param string $sName
     * @param array  $aArgv
     *
     * @return mixed
     */
    public function call($sName, $aArgv = array())
    {
        if (!$this->isCBBound($sName)) {
            throw new \OutOfBoundsException("[CTX] ; CB[$sName] has not bound");
        }
        $aArgv[] = $this;
        return call_user_func_array($this->aCB[$sName], $aArgv);
    }

    /**
     * @param string $sName
     * @param array  $aArgv
     *
     * @return mixed
     */
    public function callIgnore($sName, $aArgv = array())
    {
        if ($this->isCBBound($sName)) {
            return call_user_func_array($this->aCB[$sName], $aArgv);
        } else {
            return null;
        }
    }

    /**
     * @param string $sName
     * @param mixed  $mAny
     */
    public function bind($sName, $mAny)
    {
        $this->aData[$sName] = $mAny;
    }

    /**
     * @param array $aName2Any
     */
    public function bindMulti(array $aName2Any)
    {
        $this->aData = array_merge($this->aData, $aName2Any);
    }

    /**
     * @param string $sName
     * @param mixed  $mCB
     */
    public function bindCB($sName, $mCB)
    {
        $this->aCB[$sName] = $mCB;
    }

    /**
     * @param array $aName2CB
     */
    public function bindCBMulti(array $aName2CB)
    {
        $this->aCB = array_merge($this->aCB, $aName2CB);
    }
}
