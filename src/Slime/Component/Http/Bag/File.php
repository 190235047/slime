<?php
namespace Slime\Component\Http;

/**
 * Class Bag_File
 *
 * @package Slime\Component\Http
 * @author  smallslime@gmail.com
 */
class Bag_File extends Bag_Bag
{
    public function filter($mCB)
    {
        $aParam = func_get_args();
        foreach ($this->aData as $sName => $aItem) {
            if (!call_user_func_array($mCB, $aParam)) {
                unset($this->aData[$sName]);
            }
        }

        return $this;
    }

    /**
     * @param string $sDir
     * @param mixed  $mCBGentFileName
     *
     * @throws \RuntimeException
     */
    public function moveToDir($sDir, $mCBGentFileName = null)
    {
        if (!file_exists($sDir)) {
            if (!mkdir($sDir)) {
                throw new \RuntimeException("Create upload dir $sDir failed");
            }
        }
        if (!is_writable($sDir)) {
            throw new \RuntimeException("Upload dir $sDir is not writable");
        }
        foreach ($this->aData as $sName => $aItem) {
            $sFileName = $mCBGentFileName === null ? md5(uniqid() . rand(1, 1000)) : call_user_func(
                $mCBGentFileName,
                $aItem,
                $sName
            );
            if ($sFileName !== null && move_uploaded_file($aItem['tmp_name'], "$sDir/$sFileName") === false) {
                trigger_error("Move tmp file {$aItem['tmp_name']} to $sDir/$sFileName failed", E_USER_WARNING);
            }
        }
    }
}