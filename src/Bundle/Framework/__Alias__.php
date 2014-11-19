<?php
use Slime\Bundle\Framework\Context as CTX;

/**
 * @param string $sString
 *
 * @return string
 */
function __($sString)
{
    return (string)(CTX::inst()->I18N->get($sString));
}

/**
 * @param string $sTpl
 * @param array  $aData
 *
 * @return string
 */
function subRender($sTpl, $aData = array())
{
    return CTX::inst()->View->subRender($sTpl, $aData);
}

/**
 * @param $mItem
 *
 * @return bool
 */
function MEmpty($mItem)
{
    return \Slime\Component\RDBMS\ORM\Factory::mEmpty($mItem);
}

