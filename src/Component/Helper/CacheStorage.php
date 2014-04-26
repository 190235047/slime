<?php
namespace Slime\Component\Context;

/**
 * Class CacheStorage
 *
 * @package Slime\Component\Helper
 * @author  smallslime@gmail.com
 */
class CacheStorage
{
    const GET_BOTH_AND_SET_CACHE         = 0;
    const GET_BOTH                       = 1;
    const GET_ONLY_CACHE                 = 2;
    const GET_ONLY_STORAGE               = 3;
    const GET_ONLY_STORAGE_AND_SET_CACHE = 4;

    const SET_BOTH    = 0;
    const SET_CACHE   = 1;
    const SET_STORAGE = 2;

    const DELETE_BOTH    = 0;
    const DELETE_CACHE   = 1;
    const DELETE_STORAGE = 2;

    /**
     * @param array         $aMap [sKey:[cb_get_cache, cb_set_cache, cb_delete_cache, cb_get_storage, cb_set_storage, cb_delete_storage, CacheRead(null), CacheWrite(null), StorageRead(null), StorageWrite(null)], ... ]
     * @param null | object $CacheReadDefault
     * @param null | object $CacheWriteDefault
     * @param null | object $StorageReadDefault
     * @param null | object $StorageWriteDefault
     */
    public function __construct(
        array $aMap,
        $CacheReadDefault = null,
        $CacheWriteDefault = null,
        $StorageReadDefault = null,
        $StorageWriteDefault = null
    ) {
        $this->aMap = $aMap;
        $this->CR   = $CacheReadDefault;
        $this->CW   = $CacheWriteDefault;
        $this->SR   = $StorageReadDefault;
        $this->SW   = $StorageWriteDefault;
    }

    /**
     * @param string        $sKey
     * @param mixed         $mGetCache
     * @param mixed         $mSetCache
     * @param mixed         $mGetStorage
     * @param mixed         $mSetStorage
     * @param mixed         $mDeleteCache
     * @param mixed         $mDeleteStorage
     * @param null | Object $CacheGet
     * @param null | Object $CacheSet
     * @param null | Object $StorageGet
     * @param null | Object $StorageSet
     */
    public function register(
        $sKey,
        $mGetCache,
        $mSetCache,
        $mGetStorage,
        $mSetStorage,
        $mDeleteCache,
        $mDeleteStorage,
        $CacheGet = null,
        $CacheSet = null,
        $StorageGet = null,
        $StorageSet = null
    ) {
        $this->aMap[$sKey] = array(
            $mGetCache,
            $mSetCache,
            $mDeleteCache,
            $mGetStorage,
            $mSetStorage,
            $mDeleteStorage,
            $CacheGet,
            $CacheSet,
            $StorageGet,
            $StorageSet
        );
    }

    /**
     * @param string        $sKey
     * @param int           $IMode
     * @param null | Object $CacheRead
     * @param null | Object $StorageRead
     * @param null | Object $CacheWrite
     *
     * @return mixed
     * @throws \OutOfRangeException
     */
    public function getData(
        $sKey,
        $IMode = self::GET_BOTH_AND_SET_CACHE,
        $CacheRead = null,
        $StorageRead = null,
        $CacheWrite = null
    ) {
        if (!isset($this->aMap[$sKey])) {
            throw new \OutOfRangeException("[CACHE_ST] : $sKey is not set before");
        }

        $aQ          = $this->aMap[$sKey];
        $CacheRead   = $CacheRead === null ? (isset($aQ[6]) ? $aQ[6] : $this->CR) : $CacheRead;
        $CacheWrite  = $CacheWrite === null ? (isset($aQ[7]) ? $aQ[7] : $this->CW) : $CacheWrite;
        $StorageRead = $StorageRead === null ? (isset($aQ[8]) ? $aQ[8] : $this->SR) : $StorageRead;

        switch ($IMode) {
            case self::GET_BOTH:
                if (($mResult = call_user_func($aQ[0], $CacheRead, $sKey)) === null) {
                    $mResult = call_user_func($aQ[3], $StorageRead, $sKey);
                }
                break;
            case self::GET_ONLY_CACHE:
                $mResult = call_user_func($aQ[0], $CacheRead, $sKey);
                break;
            case self::GET_ONLY_STORAGE:
                $mResult = call_user_func($aQ[3], $StorageRead, $sKey);
                break;
            case self::GET_ONLY_STORAGE_AND_SET_CACHE:
                if (($mResult = call_user_func($aQ[3], $StorageRead, $sKey)) !== null) {
                    $mResult = call_user_func($aQ[1], $CacheWrite, $mResult, $sKey);
                }
                break;
            default:
                if (($mResult = call_user_func($aQ[0], $CacheRead)) === null) {
                    if (($mResult = call_user_func($aQ[3], $StorageRead, $sKey)) !== null) {
                        $mResult = call_user_func($aQ[1], $CacheWrite, $mResult, $sKey);
                    }
                }
                break;
        }

        return $mResult;
    }

    /**
     * @param string        $sKey
     * @param mixed         $mData
     * @param int           $IMode
     * @param null | Object $CacheWrite
     * @param null | Object $StorageWrite
     *
     * @return array|mixed
     * @throws \OutOfRangeException
     */
    public function setData($sKey, $mData, $IMode = self::SET_BOTH, $CacheWrite = null, $StorageWrite = null)
    {
        if (!isset($this->aMap[$sKey])) {
            throw new \OutOfRangeException("[CACHE_ST] : $sKey is not set before");
        }

        $aQ           = $this->aMap[$sKey];
        $CacheWrite   = $CacheWrite === null ? (isset($aQ[7]) ? $aQ[7] : $this->CW) : $CacheWrite;
        $StorageWrite = $StorageWrite === null ? (isset($aQ[9]) ? $aQ[9] : $this->SW) : $StorageWrite;

        switch ($IMode) {
            case self::SET_STORAGE:
                $mResult = call_user_func($aQ[4], $StorageWrite, $mData, $sKey);
                break;
            case self::SET_CACHE:
                $mResult = call_user_func($aQ[1], $CacheWrite, $mData, $sKey);
                break;
            default:
                $mResult = array(
                    call_user_func($aQ[1], $CacheWrite, $mData, $sKey),
                    call_user_func($aQ[4], $StorageWrite, $mData, $sKey)
                );
                break;
        }

        return $mResult;
    }

    /**
     * @param string        $sKey
     * @param int           $IMode
     * @param null | Object $CacheWrite
     * @param null | Object $StorageWrite
     *
     * @return array|mixed
     * @throws \OutOfRangeException
     */
    public function deleteData($sKey, $IMode = self::DELETE_CACHE, $CacheWrite = null, $StorageWrite = null)
    {
        if (!isset($this->aMap[$sKey])) {
            throw new \OutOfRangeException("[CACHE_ST] : $sKey is not set before");
        }

        $aQ           = $this->aMap[$sKey];
        $CacheWrite   = $CacheWrite === null ? (isset($aQ[7]) ? $aQ[7] : $this->CW) : $CacheWrite;
        $StorageWrite = $StorageWrite === null ? (isset($aQ[9]) ? $aQ[9] : $this->SW) : $StorageWrite;

        switch ($IMode) {
            case self::SET_STORAGE:
                $mResult = call_user_func($aQ[5], $CacheWrite, $sKey);
                break;
            case self::SET_CACHE:
                $mResult = call_user_func($aQ[2], $StorageWrite, $sKey);
                break;
            default:
                $mResult = array(
                    call_user_func($aQ[2], $CacheWrite, $sKey),
                    call_user_func($aQ[5], $StorageWrite, $sKey)
                );
                break;
        }

        return $mResult;
    }
}