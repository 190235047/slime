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
     * @return \Slime\Component\Support\Context
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
    public function isBound($sName, $bAttemptAutoBind = false)
    {
        if ($bAttemptAutoBind) {
            if (isset($this->aData[$sName])) {
                return true;
            } else {
                try {
                    $this->bindDataAutomatic($sName);
                } catch (\DomainException $E) {
                    return false;
                }
                return true;
            }
        } else {
            return isset($this->aData[$sName]);
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
     */
    public function bindDataAutomatic($sName)
    {
        if (isset($this->aData[$sName])) {
            return null;
        }

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
        if (isset($aArr['packer'])) {
            $Obj = new Packer($Obj, empty($aArr['packer']) || !is_array($aArr['packer']) ? array() : $aArr['packer']);
        }

        $this->aData[$sName] = $Obj;
    }

    /**
     * @param string $sName
     *
     * @return mixed
     */
    public function get($sName)
    {
        if (!$this->isBound($sName, true)) {
            throw new \OutOfBoundsException("[CTX] ; Data[$sName] has no bound");
        }
        return $this->aData[$sName];
    }

    /**
     * @param string $sName
     * @param bool   $bHasGot
     *
     * @return null
     */
    public function getIgnore($sName, &$bHasGot = true)
    {
        if ($this->isBound($sName, true)) {
            return $this->aData[$sName];
        } else {
            $bHasGot = false;
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
            throw new \OutOfBoundsException("[CTX] ; CB[$sName] has no bound");
        }
        $aArgv[] = $this;
        return call_user_func_array($this->aCB[$sName], $aArgv);
    }

    /**
     * @param string $sName
     * @param array  $aArgv
     * @param bool   $bCalled
     *
     * @return mixed|null
     */
    public function callIgnore($sName, $aArgv = array(), &$bCalled = true)
    {
        if ($this->isCBBound($sName)) {
            return call_user_func_array($this->aCB[$sName], $aArgv);
        } else {
            $bCalled = false;
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
