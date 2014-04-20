<?php
/**
 * @return {{{NS}}}\System\Context\Context
 */
function CTX()
{
    return end($GLOBALS['__SF_CONTEXT__']);
}

/**
 * @param string $sKey
 * @param mixed  $mDefaultValue
 * @param bool   $bForce
 *
 * @return mixed
 */
function CFG($sKey, $mDefaultValue = null, $bForce = false)
{
    return CTX()->Config->get($sKey, $mDefaultValue, $bForce);
}

/**
 * @param string | array $mKeyOrKeys
 * @param bool           $bXssFilter
 *
 * @return array | string | null
 */
function G($mKeyOrKeys, $bXssFilter = false)
{
    return CTX()->HttpRequest->getGetPost($mKeyOrKeys, $bXssFilter);
}

/**
 * @param string | array $mKeyOrKeys
 * @param bool           $bXssFilter
 *
 * @return array | string | null
 */
function P($mKeyOrKeys, $bXssFilter = false)
{
    return CTX()->HttpRequest->getGetPost($mKeyOrKeys, $bXssFilter);
}

/**
 * @param string | array $mKeyOrKeys
 * @param bool           $bGetFirst
 * @param bool           $bXssFilter
 *
 * @return array | string | null
 */
function GP($mKeyOrKeys, $bGetFirst = true, $bXssFilter = false)
{
    return CTX()->HttpRequest->getGetPost($mKeyOrKeys, $bGetFirst, $bXssFilter);
}

/**
 * @param string | array $mKeyOrKeys
 * @param bool           $bXssFilter
 *
 * @return array | string | null
 */
function C($mKeyOrKeys, $bXssFilter = false)
{
    return CTX()->HttpRequest->getCookie($mKeyOrKeys, $bXssFilter);
}

/**
* @param string | array $mKeyOrKVMap

* @return string | null
*/
function REQ_H($sKey)
{
    CTX()->HttpRequest->Header[$sKey];
}

/**
 * @param string | array $mKeyOrKVMap
 * @param string | null  $sValue
 * @param bool           $bOverWrite
 */
function RES_H($mKeyOrKVMap, $sValue = null, $bOverWrite = true)
{
    CTX()->HttpResponse->setHeader($mKeyOrKVMap, $sValue, $bOverWrite);
}

/**
 * @param string | null $sUrl
 */
function RES_HJump($sUrl = null)
{
    $CTX = CTX();
    if ($sUrl === null) {
        $sReferer = $CTX->HttpRequest->getHeader('Referer');
        $sUrl     = $sReferer === null ? '/' : $sReferer;
    }
    $CTX->HttpResponse->setRedirect($sUrl);
}

/**
 * @param string $sString
 *
 * @return string
 */
function __($sString)
{
    return (string)(CTX()->I18N->get($sString));
}

/**
 * @param string $sTpl
 * @param array  $aData
 *
 * @return string
 */
function subRender($sTpl, $aData = array())
{
    return CTX()->View->subRender($sTpl, $aData);
}

/**
 * @return \{{{NS}}}\System\Model\Factory_Base
 */
function MF()
{
    return CTX()->ModelFactory;
}
