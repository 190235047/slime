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
     * @param \Slime\Component\Http\REQ $HttpRequest
     * @param string                    $sLanguageBaseDir
     * @param string                    $sDefaultLanguageDir
     * @param string                    $sCookieKey
     *
     * @return I18N
     */
    public static function createFromHttp(
        $HttpRequest,
        $sLanguageBaseDir,
        $sDefaultLanguageDir = 'english',
        $sCookieKey = null
    ) {
        $nsLangFromC = null;
        if ($sCookieKey !== null) {
            $nsLangFromC = $HttpRequest->getC($sCookieKey);
        }
        $nsLangFromH = $HttpRequest->getHeader('Accept_Language');
        if (empty($nsLangFromC)) {
            if ($nsLangFromH === null) {
                $sLang = 'en-us';
            } else {
                $sLang = strtolower(strtok($nsLangFromH, ','));
            }
        } else {
            $sLang = $nsLangFromC;
        }

        return new self($sLanguageBaseDir, $sLang, $sDefaultLanguageDir);
    }

    public static function createFromCli($sLanguageBaseDir, array $aArg, $sDefaultLanguageDir = 'english')
    {
        $sLanguage = $aArg[count($aArg) - 1];
        if (array_search($sLanguage, self::$aLangMapDir) === false) {
            $sLanguage = $sDefaultLanguageDir;
        }

        return new self($sLanguageBaseDir, $sLanguage, $sDefaultLanguageDir);
    }

    public function __construct($sLanguageBaseDir, $sLanguage, $sDefaultLanguageDir, $sConfigAdaptor = '@PHP')
    {
        $sCurrentLanguageDir = null;
        foreach (self::$aLangMapDir as $sK => $sV) {
            if (preg_match($sK, $sLanguage)) {
                $sCurrentLanguageDir = $sV;
                break;
            }
        }

        $this->sLangDir = $sCurrentLanguageDir;

        $this->Configure = Config\Configure::factory(
            $sConfigAdaptor,
            $sLanguageBaseDir . DIRECTORY_SEPARATOR . $sCurrentLanguageDir,
            $sLanguageBaseDir . DIRECTORY_SEPARATOR . $sDefaultLanguageDir
        );
    }

    public function get($sString)
    {
        return $this->Configure->get($sString);
    }
}