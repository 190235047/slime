<?php
namespace Slime\Component\Helper;

/**
 * Class Arr
 *
 * @package Slime\Component\Helper
 * @author  smallslime@gmail.com
 */
class Arr
{
    /**
     * @param array  $aArr
     * @param string $sKey
     *
     * @return null
     */
    public static function get(array $aArr, $sKey)
    {
        return isset($aArr[$sKey]) ? $aArr[$sKey] : null;
    }

    /**
     * @param array  $aArr
     * @param string $sKey
     *
     * @return mixed
     * @throws \Exception
     */
    public static function getForce(array $aArr, $sKey)
    {
        if (!array_key_exists($sKey, $aArr)) {
            throw new \Exception("$sKey is not in array" . json_encode($aArr));
        }
        return $aArr[$sKey];
    }

    /**
     * @param array  $aArr
     * @param string $sKey
     * @param array  $aExcept
     * @param array  $aLeft
     *
     * @return array
     */
    public static function changeIndex(array $aArr, $sKey = 'id', array $aExcept = null, array $aLeft = null)
    {
        $aResult = array();
        if (!empty($aLeft)) {
            foreach ($aArr as $aItem) {
                $aRow = array();
                foreach ($aLeft as $sLeftKey) {
                    $aRow[$sLeftKey] = $aItem[$sLeftKey];
                }
                $aResult[$aItem[$sKey]] = $aRow;
            }
        } elseif (!empty($aExcept)) {
            foreach ($aArr as $aItem) {
                foreach ($aExcept as $sExceptKey) {
                    unset($aItem[$sExceptKey]);
                }
                $aResult[$aItem[$sKey]] = $aItem;
            }
        } else {
            foreach ($aArr as $aItem) {
                $aResult[$aItem[$sKey]] = $aItem;
            }
        }
        return $aResult;
    }

    /**
     * @param array  $aArr
     * @param string $sKeyName
     * @param string $sValueName
     *
     * @return array
     */
    public static function changeIndexToKVMap(array $aArr, $sKeyName, $sValueName)
    {
        $aResult = array();
        foreach ($aArr as $aItem) {
            $aResult[$aItem[$sKeyName]] = $aItem[$sValueName];
        }
        return $aResult;
    }

    /**
     * @param array  $aArr
     * @param string $sKey
     *
     * @return array
     */
    public static function getIndex(array $aArr, $sKey = 'id')
    {
        $aResult = array();
        foreach ($aArr as $aItem) {
            $aResult = $aItem[$sKey];
        }
        return $aResult;
    }
}
