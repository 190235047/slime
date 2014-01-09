<?php
namespace Slime\Helper;

class Sugar
{

    public static function tryIt($mTryFunc, array $aParam = array(), $iMaxTimes = -1, $iSleepMS = null)
    {
        $i     = 0;
        $mData = null;
        while ($iMaxTimes <= 0 || $i < $iMaxTimes) {
            if (($mData = call_user_func_array($mTryFunc, $aParam)) !== null) {
                break;
            }
            if ($iSleepMS > 0) {
                usleep($iSleepMS * 1000);
            }
            ++$i;
        }
        return $mData;
    }
}