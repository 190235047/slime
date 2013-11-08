<?php
namespace Slime\Component\I18N;

use Slime\Component\Config;
use Slime\Component\Http;

class I18N
{
    public static $aLangMapDir = array(
        '#en-.*#' => 'english',
        '#zh-.*#' => 'zh-cn'
    );

    public static function createFromHttp(
        $sLanguageBaseDir,
        Http\HttpRequest $HttpRequest,
        $sDefaultLanguageDir = 'english',
        $sCookieKey = null
    )
    {
        $sLanguage = null;
        if ($sCookieKey!==null) {
            $sLanguage = $HttpRequest->getCookie($sCookieKey);
        }
        $sLanguage = empty($sLanguage) ?
            strtolower(strtok($HttpRequest->getHeader('Accept-Language'), ',')) :
            $sLanguage;

        return new self($sLanguageBaseDir, $sLanguage, $sDefaultLanguageDir);
    }

    public static function createFromCli($sLanguageBaseDir, array $aArg, $sDefaultLanguageDir = 'english')
    {
        $sLanguage = $aArg[count($aArg)-1];
        if (array_search($sLanguage, self::$aLangMapDir)===false) {
            $sLanguage = $sDefaultLanguageDir;
        }

        return new self($sLanguageBaseDir, $sLanguage, $sDefaultLanguageDir);
    }

    public function __construct($sLanguageBaseDir, $sLanguage, $sDefaultLanguageDir)
    {
        $sCurrentLanguageDir = null;
        foreach (self::$aLangMapDir as $sK => $sV) {
            if (preg_match($sK, $sLanguage)) {
                $sCurrentLanguageDir = $sV;
                break;
            }
        }

        $this->Configure = Config\Configure::factory(
            '@PHP',
            $sLanguageBaseDir . DIRECTORY_SEPARATOR . $sCurrentLanguageDir,
            $sLanguageBaseDir . DIRECTORY_SEPARATOR . $sDefaultLanguageDir
        );
    }

    public function get($sString)
    {
        return $this->Configure->get($sString);
    }
}