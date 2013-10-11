<?php
namespace Slime\Component\Log;

class Writer_STDFD implements IWriter
{
    public $sFormat = '[:sGuid][:iLevel] : :sTime , :sMessage';

    public function acceptData($aRow)
    {
        $sStr = str_replace(
                array(':sTime', ':iLevel', ':sMessage', ':sGuid'),
                array($aRow['sTime'], Logger::getLevelString($aRow['iLevel']), $aRow['sMessage'], $aRow['sGuid']),
                $this->sFormat
            ) . PHP_EOL;

        if ($aRow['iLevel'] == Logger::LEVEL_DEBUG) {
            fprintf(STDOUT, $sStr, null);
        } else {
            fprintf(STDERR, $sStr, null);
        }
    }
}