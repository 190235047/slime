<?php
namespace Slime\Bundle\Framework;

use SlimeCMS\System\Support\CTX;

class Ext
{
    public static function hUncaught(\Exception $E)
    {
        $CTX = CTX::inst();
        $CTX->Log->error($E->getMessage());
        if ($CTX->isBound('RESP')) {
            if ($CTX->RESP->getStatus() < 400) {
                $CTX->RESP->setStatus(500);
            }
            $aArr = $E->getTrace();
            foreach ($aArr as $iK => $aItem) {
                if (isset($aItem['args'])) {
                    unset($aArr[$iK]['args']);
                }
            }
            $CTX->RESP->setBody(sprintf(
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

    public static function hError($iErrNum, $sErrStr, $sErrFile, $iErrLine, $sErrContext)
    {
        $CTX = CTX::inst();
        $sStr = $iErrNum . ':' . $sErrStr . "\nIn File[$sErrFile]:Line[$iErrLine]";

        switch ($iErrNum) {
            case E_NOTICE:
            case E_USER_NOTICE:
                $CTX->Log->notice($sStr);
                break;
            case E_USER_ERROR:
                throw new \ErrorException($sStr);
            default:
                $CTX->Log->warning($sStr);
                break;
        }
    }
}