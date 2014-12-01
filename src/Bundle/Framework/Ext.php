<?php
namespace Slime\Bundle\Framework;

use Slime\Component\Support\Context;

class Ext
{
    public static function handleUncaughtException(\Exception $E)
    {
        $C = Context::inst();
        $C->Log->error($E->getMessage());
        if ($C->sRunMode === 'http') {
            if ($C->RESP->getStatus() < 400) {
                $C->RESP->setStatus(500);
            }
            $aArr = $E->getTrace();
            foreach ($aArr as $iK => $aItem) {
                if (isset($aItem['args'])) {
                    unset($aArr[$iK]['args']);
                }
            }
            $C->RESP->setBody(sprintf(
                '<h1>%s</h1><h2>%d:%s</h2><h3>File:%s;Line:%s</h3><div><pre>%s</pre></div>',
                get_class($E),
                $E->getCode(),
                $E->getMessage(),
                $E->getFile(),
                $E->getLine(),
                var_export($aArr, true)
            ))->send();
        }
        exit(1);
    }

    public static function handleError($iErrNum, $sErrStr, $sErrFile, $iErrLine, $sErrContext)
    {
        $C    = Context::inst();
        $sStr = $iErrNum . ':' . $sErrStr . "\nIn File[$sErrFile]:Line[$iErrLine]";

        switch ($iErrNum) {
            case E_NOTICE:
            case E_USER_NOTICE:
                $C->Log->notice($sStr);
                break;
            case E_USER_ERROR:
                throw new \ErrorException($sStr);
            default:
                $C->Log->warning($sStr);
                break;
        }
    }
}