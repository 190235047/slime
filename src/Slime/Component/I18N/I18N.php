<?php
namespace Slime\Component\I18N;

use Slime\Component\Config;

class I18N
{
    public static $sCookieKey = '__sf_language__';

    public static $aLangMapDir = array(
        '#en-.*#' => 'english',
        '#zh-.*#' => 'zh-cn'
    );

    public static $sDefaultLangDir = 'english';

    public function __construct($sBaseDir, $sLang = null)
    {
        if ($sLang === null) {
            $sLang = empty($_COOKIE[self::$sCookieKey]) ? strtolower(
                strtok(strip_tags($_SERVER['HTTP_ACCEPT_LANGUAGE']), ',')
            ) : $_COOKIE[self::$sCookieKey];
        }
        $sLangDir = null;
        foreach (self::$aLangMapDir as $sK => $sV) {
            if (preg_match($sK, $sLang)) {
                $sLangDir = $sV;
                break;
            }
        }
        $this->sLangDir  = $sLangDir === null ? self::$sDefaultLangDir : $sLangDir;
        $this->Configure = new Config\Configure(
            '@PHP',
            $sBaseDir . DIRECTORY_SEPARATOR . $sLangDir,
            $sBaseDir . DIRECTORY_SEPARATOR . self::$sDefaultLangDir
        );
    }

    public function get($sString)
    {
        return $this->Configure->get($sString);
    }
}