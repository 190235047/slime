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
    public function moveTo($sDir, $mCBGentFileName = null, $mCBFilter = null)
    {
        $sDir = rtrim($sDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (!is_dir($sDir)) {
            if (!mkdir($sDir, 0777, true)) {
                throw new \RuntimeException("[HTTP_REQ_FILE] : Create upload dir $sDir failed");
            }
        }

        $aData = $mCBFilter === null ? $this->aData : array_filter($this->aData, $mCBFilter);

        $aResult = array();
        foreach ($aData as $sName => $aItem) {
            $sFileName     = $mCBGentFileName === null ? md5(uniqid('', true)) : call_user_func(
                $mCBGentFileName,
                $aItem,
                $sName
            );
            $iFileExtDot = strrpos($aItem['name'], '.');
            $sFilePath   = sprintf(
                '%s%s%s',
                $sDir,
                $sFileName,
                $iFileExtDot === false ? '' : ('.' . substr($aItem['name'], $iFileExtDot + 1))
            );
            if (move_uploaded_file($aItem['tmp_name'], $sFilePath) === false) {
                $aResult[$sName] = array('error' => $aItem['error']);
            } else {
                $aResult[$sName] = array(
                    'error'         => 0,
                    'type'          => $aItem['type'],
                    'size'          => $aItem['size'],
                    'file_path'     => $sFilePath
                );
            }
        }
        return $aResult;
    }
}