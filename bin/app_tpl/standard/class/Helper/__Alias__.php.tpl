<?php
/**
 * @param string $sKey
 * @param mixed  $mDefaultValue
 * @param bool   $bForce
 *
 * @return mixed
 */
function CFG($sKey, $mDefaultValue = null, $bForce = false)
{
    return Context::getInst()->Config->get($sKey, $mDefaultValue, $bForce);
}

/**
 * @param string | array $mKeyOrKeys
 * @param bool           $bXssFilter
 *
 * @return array | string | null
 */
function G($mKeyOrKeys, $bXssFilter = false)
{
    return Context::getInst()->HttpRequest->getGetPost($mKeyOrKeys, $bXssFilter);
}

/**
 * @param string | array $mKeyOrKeys
 * @param bool           $bXssFilter
 *
 * @return array | string | null
 */
function P($mKeyOrKeys, $bXssFilter = false)
{
    return Context::getInst()->HttpRequest->getGetPost($mKeyOrKeys, $bXssFilter);
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
    return Context::getInst()->HttpRequest->getGetPost($mKeyOrKeys, $bGetFirst, $bXssFilter);
}

/**
 * @param string | array $mKeyOrKeys
 * @param bool           $bXssFilter
 *
 * @return array | string | null
 */
function C($mKeyOrKeys, $bXssFilter = false)
{
    return Context::getInst()->HttpRequest->getCookie($mKeyOrKeys, $bXssFilter);
}

/**
* @param string | array $mKeyOrKVMap

* @return string | null
*/
function REQ_H($sKey)
{
    Context::getInst()->HttpRequest->Header[$sKey];
}

/**
 * @param string | array $mKeyOrKVMap
 * @param string | null  $sValue
 * @param bool           $bOverWrite
 */
function RES_H($mKeyOrKVMap, $sValue = null, $bOverWrite = true)
{
    Context::getInst()->HttpResponse->setHeader($mKeyOrKVMap, $sValue, $bOverWrite);
}

/**
 * @param string | null $sUrl
 */
function RES_HJump($sUrl = null)
{
    $CTX = Context::getInst();
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
    return (string)Context::getInst()->I18N->get($sString);
}

/**
 * @param string $sTpl
 * @param array  $aData
 *
 * @return string
 */
function subRender($sTpl, $aData = array())
{
    return Context::getInst()->View->subRender($sTpl, $aData);
}

/**
 * @param string $sTpl
 * @param array  $aData
 *
 * @return \{{{NS}}}\System\Model\Factory_Base
 */
function MF()
{
    return Context::getInst()->ModelFactory;
}