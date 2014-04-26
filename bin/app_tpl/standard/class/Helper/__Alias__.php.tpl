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
 *
 * @return array | string | null
 */
function G($mKeyOrKeys)
{
    return CTX()->HttpRequest->getG($mKeyOrKeys);
}

/**
 * @param string | array $mKeyOrKeys
 *
 * @return array | string | null
 */
function P($mKeyOrKeys)
{
    return CTX()->HttpRequest->getP($mKeyOrKeys);
}

/**
 * @param string | array $mKeyOrKeys
 *
 * @return array | string | null
 */
function GP($mKeyOrKeys)
{
    return CTX()->HttpRequest->getGP($mKeyOrKeys);
}

/**
 * @param string | array $mKeyOrKeys
 *
 * @return array | string | null
 */
function C($mKeyOrKeys)
{
    return CTX()->HttpRequest->getC($mKeyOrKeys);
}

/**
 * @param string $sKey
 * @return string | null
 */
function REQ_H($sKey)
{
    CTX()->HttpRequest->getHeader($sKey);
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
    $CTX->HttpResponse->setHeaderRedirect($sUrl);
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
