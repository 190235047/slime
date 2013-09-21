<?php
namespace SlimeFramework\Component\HTML;

/**
 * Class Form
 * @package SlimeFramework\Component\HTML
 * @author smallslime@gmail.com
 * @version 0.1
 */
class Form
{
    const TEXT = 1;
    const PASSWD = 2;
    const OPTION = 3;
    const RADIO = 4;

    public static function fill($sKey, $iType = self::TEXT, $mAttr = '', $bKeyFromGet = true)
    {
        $sValue = $bKeyFromGet ? (isset($_GET[$sKey]) ? $_GET[$sKey] : '') : $sKey;
        switch ($iType) {
            case self::TEXT:
                return $sValue ? htmlentities($_GET[$sKey]) : '';
            case self::OPTION:
                return $sValue===(string)$mAttr ? 'selected = "selected"' : '';
            default:
                trigger_error('');
                exit(1);
        }
    }

    public static function fillInputTextFromGet($sGetKey, $sDefaultValue = '') {
        return isset($_GET[$sGetKey]) ? htmlentities($_GET[$sGetKey]) : $sDefaultValue;
    }
}
