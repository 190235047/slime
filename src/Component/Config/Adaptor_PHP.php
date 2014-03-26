<?php
namespace Slime\Component\Config;

/**
 * Class Configure
 *
 * @package Slime\Component\Config
 * @author  smallslime@gmail.com
 */
class Adaptor_PHP extends Adaptor_ABS
{
    /** @var string */
    private $sBaseDir;

    /** @var string */
    private $sDefaultBaseDir;

    /** @var bool */
    private $bIsDefault;

    /** @var array */
    private $aCachedData;

    /**
     * @param string $sBaseDir
     * @param string $sDefaultBaseDir
     */
    public function __construct($sBaseDir, $sDefaultBaseDir)
    {
        $this->sBaseDir        = $sBaseDir;
        $this->sDefaultBaseDir = $sDefaultBaseDir;
        $this->bIsDefault      = $this->sBaseDir === $this->sDefaultBaseDir;
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
        if ($this->bIsDefault) {
            $mResult = $this->_get($sKey, $this->sDefaultBaseDir);
        } else {
            $mDefaultResult    = $this->_get($sKey, $this->sDefaultBaseDir);
            $mCurrentENVResult = $this->_get($sKey, $this->sBaseDir);
            $mResult           = $mCurrentENVResult === null ? $mDefaultResult : $mCurrentENVResult;
        }
        if ($mResult === null && $bForce) {
            throw new \OutOfRangeException("[CONFIG] : Key [{$sKey}] is not exsi");
        }
        return $mResult;
    }

    protected function _get($sKey, $sBaseDir)
    {
        if (strpos($sKey, '.') === false) {
            $sK    = $sKey;
            $aKeys = null;
        } else {
            $aKeys = explode('.', $sKey);
            $sK    = array_shift($aKeys);
        }

        if (!isset($this->aCachedData[$sBaseDir][$sK])) {
            $sConfFile = $sBaseDir . '/' . str_replace(':', '/', $sK) . '.php';
            $mResult   = file_exists($sConfFile) ? require $sConfFile : null;
            $mResult   = $this->bParseMode ? Configure::parseRecursion($mResult, $this) : $mResult;

            $this->aCachedData[$sBaseDir][$sK] = $mResult;
        } else {
            $mResult = $this->aCachedData[$sBaseDir][$sK];
        }

        if ($aKeys !== null) {
            foreach ($aKeys as $sKey) {
                if (!isset($mResult[$sKey])) {
                    $mResult = null;
                    break;
                }
                $mResult = $mResult[$sKey];
            }
        }

        return $mResult;
    }
}