<?php
namespace Slime\Component\Http;

/**
 * Class Bag_File
 *
 * @package Slime\Component\Http
 * @author  smallslime@gmail.com
 */
class Bag_File extends Bag_Base
{
    /**
     * @param string $sDir
     * @param mixed  $mCBGentFileName
     * @param mixed  $mCBFilter
     *
     * @return array
     * @throws \RuntimeException
     */
    public function moveToDir($sDir, $mCBGentFileName = null, $mCBFilter = null)
    {
        if (!file_exists($sDir)) {
            if (!mkdir($sDir)) {
                throw new \RuntimeException("[REQ_FILE] : Create upload dir $sDir failed");
            }
        }
        if (!is_writable($sDir)) {
            throw new \RuntimeException("[REQ_FILE] : Upload dir $sDir is not writable");
        }
        $aData = $mCBFilter === null ? $this->aData : array_filter($this->aData, $mCBFilter);

        $aResult = array();
        foreach ($aData as $sName => $aItem) {
            $sFileName     = $mCBGentFileName === null ? md5(uniqid() . rand(1, 1000)) : call_user_func(
                $mCBGentFileName,
                $aItem,
                $sName
            );
            $iFileExtDot   = strrpos($aItem['name'], '.');
            $sFileExt      = $iFileExtDot === false ? '' : substr($aItem['name'], $iFileExtDot + 1);
            $sFileFullName = $sFileName . ($sFileExt === '' ? '' : ".{$sFileExt}");
            $sFilePath     = rtrim($sDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $sFileFullName;
            if (!file_exists($sFilePath)) {
                //@todo is_upload_file
                if (move_uploaded_file($aItem['tmp_name'], $sFilePath) === false) {
                    $aResult[$sName] = array(
                        'error'         => $aItem['error'],
                        'filed_name'    => $sName,
                        'org_file_name' => '',
                        'file_name'     => '',
                        'file_ext'      => '',
                        'file_fullname' => '',
                        'file_path'     => '',
                        'type'          => '',
                        'size'          => ''
                    );
                } else {
                    $aResult[$sName] = array(
                        'error'         => 0,
                        'name'          => $sName,
                        'org_file_name' => $aItem['name'],
                        'type'          => $aItem['type'],
                        'size'          => $aItem['size'],
                        'file_name'     => $sFileName,
                        'file_ext'      => $sFileExt,
                        'file_fullname' => $sFileFullName,
                        'file_path'     => $sFilePath,
                    );
                }
            } else {
                $aResult[$sName] = array(
                    'error'         => -1,
                    'name'          => $sName,
                    'org_file_name' => $aItem['name'],
                    'type'          => $aItem['type'],
                    'size'          => $aItem['size'],
                    'file_name'     => $sFileName,
                    'file_ext'      => $sFileExt,
                    'file_fullname' => $sFileFullName,
                    'file_path'     => $sFilePath,
                );
            }
        }
        return $aResult;
    }
}