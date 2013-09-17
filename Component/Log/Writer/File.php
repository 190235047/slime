<?php
namespace SlimeFramework\Component\Log;

class Writer_File implements IWriter
{
    public $sFormat = '[:iLevel] : :sTime , :sGuid , :sMessage';

    public $iBuffer;
    public $sFilePath;

    protected $aData;

    public function __construct($sFilePath, $iBuffer = 50)
    {
        $this->sFilePath = $sFilePath;
        $this->iBuffer = $iBuffer;
    }

    public function acceptData($aRow)
    {
        $this->aData[] = str_replace(
            array(':sTime', ':iLevel', ':sMessage', ':sGuid'),
            array($aRow['sTime'], Logger::getLevelString($aRow['iLevel']), $aRow['sMessage'], $aRow['sGuid']),
            $this->sFormat
        ) . PHP_EOL;

        if (count($this->aData) >= $this->iBuffer) {
            $this->flush2File();
        }
    }

    public function flush2File()
    {
        if (!empty($this->aData)) {
            file_put_contents($this->sFilePath, implode(PHP_EOL, $this->aData) . PHP_EOL, FILE_APPEND);
            $this->aData = array();
        }
    }

    public function __destruct()
    {
        $this->flush2File();
    }
}