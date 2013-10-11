<?php
namespace Slime\Component\Config;

use Slime\Component\Config\IAdaptor;
use Slime\Component\Log\Logger;

/**
 * Class Configure
 *
 * @package Slime\Component\Config
 * @author  smallslime@gmail.com
 * @version 1.0
 */
class Adaptor_PHP implements IAdaptor
{
    private $sBaseDir;
    private $sDefaultBaseDir;

    private $bIsDefault;

    private $aCachedData;

    public function __construct($sBaseDir, $sDefaultBaseDir, Logger $Log)
    {
        $this->sBaseDir        = $sBaseDir;
        $this->sDefaultBaseDir = $sDefaultBaseDir;

        $this->bIsDefault = $this->sBaseDir === $this->sDefaultBaseDir;
        $this->Log        = $Log;
    }

    /**
     * @param string $sKey
     * @param mixed  $sDefaultValue
     * @param bool   $bForce
     *
     * @return mixed
     */
    public function get($sKey, $sDefaultValue = null, $bForce = false)
    {
        if ($this->bIsDefault) {
            $mResult = $this->_get($sKey, $this->sDefaultBaseDir);
        } else {
            $mDefaultResult    = $this->_get($sKey, $this->sDefaultBaseDir);
            $mCurrentENVResult = $this->_get($sKey, $this->sBaseDir);
            $mResult           = $mCurrentENVResult === null ?
                $mDefaultResult :
                (
                is_array($mDefaultResult) ?
                    array_merge($mDefaultResult, $mCurrentENVResult) :
                    $mCurrentENVResult
                );
        }
        if ($mResult === null && $bForce) {
            $this->Log->error('config {key} is not found', array('key' => $sKey));
            exit(1);
        }
        return $mResult;
    }

    private function _get($sKey, $sBaseDir)
    {
        if (!strpos($sKey, '.')) {
            if (!isset($this->aCachedData[$sBaseDir][$sKey])) {
                $sConfigFile                         = $sBaseDir . '/' . str_replace(':', '/', $sKey) . '.php';
                $this->aCachedData[$sBaseDir][$sKey] = file_exists($sConfigFile) ? require $sConfigFile : null;
            }
            $mResult = $this->aCachedData[$sBaseDir][$sKey];
        } else {
            $aKeys = explode('.', $sKey);
            $sFile = array_shift($aKeys);
            if (!isset($this->aCachedData[$sBaseDir][$sFile])) {
                $sConfigFile                          = $sBaseDir . '/' . str_replace(':', '/', $sFile) . '.php';
                $this->aCachedData[$sBaseDir][$sFile] = file_exists($sConfigFile) ? require $sConfigFile : null;
            }
            $mResult = $this->aCachedData[$sBaseDir][$sFile];
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