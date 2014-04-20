<?php
namespace Slime\Component\Log;

/**
 * Class Writer_File
 *
 * @package Slime\Component\Log
 * @author  smallslime@gmail.com
 */
class Writer_File implements IWriter
{
    public $sFormat = '[:iLevel] : :sTime ; :sGuid ; :sMessage';

    protected $aData;
    protected $iNumber;

    public function __construct(
        $sFilePathFormat,
        $iBuffer = 50,
        $aLevelMap = array(
            Logger::LEVEL_DEBUG => 'access',
            Logger::LEVEL_INFO  => 'access',
            -1                  => 'error'
        ),
        $mCBPath = null
    )
    {
        $this->sFilePathFormat = $sFilePathFormat;
        $this->iBuffer         = $iBuffer;
        $this->aLevelMap       = $aLevelMap;
        $this->mCBPath         = $mCBPath;
    }

    public function acceptData($aRow)
    {
        $sFilePath = $this->mCBPath === null ?
            sprintf(
                $this->sFilePathFormat,
                date('Y-m-d'),
                (isset($this->aLevelMap[$aRow['iLevel']]) ? $this->aLevelMap[$aRow['iLevel']] : $this->aLevelMap[-1])
            ) :
            call_user_func($this->mCBPath, $this->sFilePathFormat, $this->aLevelMap, $aRow);

        $this->aData[$sFilePath][] = str_replace(
                array(':sTime', ':iLevel', ':sMessage', ':sGuid'),
                array($aRow['sTime'], Logger::getLevelString($aRow['iLevel']), $aRow['sMessage'], $aRow['sGuid']),
                $this->sFormat
            ) . PHP_EOL;
        $this->iNumber++;

        if ($this->iNumber >= $this->iBuffer) {
            $this->flush2File();
        }
    }

    public function flush2File()
    {
        foreach ($this->aData as $sFilePath => $aData) {
            file_put_contents($sFilePath, implode(PHP_EOL, $aData) . PHP_EOL, FILE_APPEND);
            $this->aData[$sFilePath] = array();
        }

        $this->iNumber = 0;
    }

    public function __destruct()
    {
        if ($this->iNumber > 0) {
            $this->flush2File();
        }
    }
}
