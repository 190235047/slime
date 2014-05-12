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
    public function __construct(
        $sFileFormat,
        $nsContentFormat = null,
        $naLevelMap = null,
        $aVarMap = null
    ) {
        $this->sFileFormat    = $sFileFormat;
        $this->sContentFormat = $nsContentFormat === null ? '[{iLevel}] : {sTime} ; {sGuid} ; {sMessage}' : (string)$nsContentFormat;
        $this->aVarMap        = $aVarMap === null ? array('{date}' => date('Y-m-d')) : (array)$aVarMap;
        $this->aLevelMap      = $naLevelMap === null ? array(
            Logger::LEVEL_DEBUG => 'access',
            Logger::LEVEL_INFO  => 'access',
            -1                  => 'error'
        ) : (array)$naLevelMap;
    }

    public function acceptData($aRow)
    {
        $aVarMap = array();
        if (!isset($this->aVarMap['{level}'])) {
            $aVarMap['{level}'] = isset($this->aLevelMap[$aRow['iLevel']]) ?
                $this->aLevelMap[$aRow['iLevel']] :
                $this->aLevelMap[-1];
        }
        if (!isset($this->aVarMap['{date}'])) {
            $aVarMap['{date}'] = date('Y-m-d');
        }
        $aVarMap   = array_merge($aVarMap, $this->aVarMap);
        $sFilePath = str_replace(array_keys($aVarMap), array_values($aVarMap), $this->sFileFormat);

        $sStr = str_replace(
                array('{sTime}', '{iLevel}', '{sMessage}', '{sGuid}'),
                array($aRow['sTime'], Logger::getLevelString($aRow['iLevel']), $aRow['sMessage'], $aRow['sGuid']),
                $this->sContentFormat
            ) . PHP_EOL;

        file_put_contents($sFilePath, implode(PHP_EOL, $sStr) . PHP_EOL, FILE_APPEND);
    }
}
