<?php
namespace Slime\Component\I18N;

use Slime\Component\Config;

/**
 * Class I18N
 *
 * @package Slime\Component\I18N
 * @author  smallslime@gmail.com
 */
class I18N
{
    public static $aLangMapDir = array(
        '#en-.*#' => 'english',
        '#zh-.*#' => 'zh-cn'
    );

    /**
     * @param \Slime\Component\Http\REQ $REQ
     * @param string                    $sLangBaseDir
     * @param string                    $sDefaultLangDir
     * @param string                    $sCookieKey
     *
     * @return I18N
     */
    public static function createFromHttp(
        $REQ,
        $sLangBaseDir,
        $sDefaultLangDir = 'english',
        $sCookieKey = null
    ) {
        $nsLangFromC = null;
        if ($sCookieKey !== null) {
            $nsLangFromC = $REQ->getC($sCookieKey);
        }
        $nsLangFromH = $REQ->getHeader('Accept_Language');
        if (empty($nsLangFromC)) {
            if ($nsLangFromH === null) {
                $sLang = 'en-us';
            } else {
                $sLang = strtolower(strtok($nsLangFromH, ','));
            }
        } else {
            $sLang = $nsLangFromC;
        }

        return new self($sLangBaseDir, $sLang, $sDefaultLangDir);
    }

    public static function createFromCli(array $aArg, $sLanguageBaseDir, $sDefaultLanguageDir = 'english')
    {
        $sLanguage = $aArg[count($aArg) - 1];
        if (array_search($sLanguage, self::$aLangMapDir) === false) {
            $sLanguage = $sDefaultLanguageDir;
        }

        return new self($sLanguageBaseDir, $sLanguage, $sDefaultLanguageDir);
    }

    public function __construct($sLangBaseDir, $sLang, $sDefaultLanguageDir, $sConfigAdaptor = '@PHP')
    {
        $sCurrentLanguageDir = null;
        foreach (self::$aLangMapDir as $sK => $sV) {
            if (preg_match($sK, $sLang)) {
                $sCurrentLanguageDir = $sV;
                break;
            }
        }

        $this->sLangDir = $sCurrentLanguageDir;

        $this->Obj = Config\Configure::factory(
            $sConfigAdaptor,
            $sLangBaseDir . DIRECTORY_SEPARATOR . $sCurrentLanguageDir,
            $sLangBaseDir . DIRECTORY_SEPARATOR . $sDefaultLanguageDir
        );
    }

    public function get($sString)
    {
        return $this->Obj->get($sString);
    }
}