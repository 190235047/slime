<?php
namespace Slime\Component\Helper;

/**
 * Class EasyXML
 *
 * @package Slime\Component\Helper
 * @author  smallslime@gmail.com
 *
 * @usage   :
 *          $aArr = array(
 *              'books' => array(
 *                  'book' => '\@The name with @first', //easy way if no index duplicate
 *                  array(
 *                      'book' => 'Common english book',
 *                      '__attr__' => array('important' => '0', 'has_read' => '1'),
 *                  ),
 *                  array(
 *                      'book' => '@有中文需CDATA',
 *                      '__attr__' => array('important' => '1', 'has_read' => '1'),
 *                  ),
 *              ),
 *              'buyer' => 'smallslime'
 *          );
 *
 *          $sXML = EasyXML::Array2XML($aArr);
 *          var_dump($sXML);
 *          var_dump(EasyXML::XML2Array($sXML));
 *          var_dump(EasyXML::XML2Array($sXML, true));
 *          var_dump(EasyXML::XML2Array($sXML, true, 'root', '1.0', 'gbk', function($sStr){return iconv('utf-8', 'gbk', $sStr);}));
 */
class EasyXML
{
    /**
     * @param string       $sXML
     * @param bool         $bAutoAtWhenCDATA
     * @param string       $sRoot
     * @param string       $sVersion
     * @param string       $sCharset
     * @param mixed | null $nmCBIconv
     *
     * @return array|string
     */
    public static function XML2Array(
        $sXML,
        $bAutoAtWhenCDATA = false,
        $sRoot = 'root',
        $sVersion = '1.0',
        $sCharset = 'utf-8',
        $nmCBIconv = null
    ) {
        $DOM = new \DOMDocument($sVersion, $sCharset);
        $DOM->loadXML($sXML);
        $Root = $DOM->getElementsByTagName($sRoot)->item(0);
        $aArr = self::ParseXML($Root, $bAutoAtWhenCDATA, $nmCBIconv);
        return $aArr[$sRoot];
    }

    /**
     * @param \DOMNode     $Node
     * @param bool         $bAutoAtWhenCDATA
     * @param mixed | null $nmCBIconv
     *
     * @return array|string
     */
    public static function ParseXML(\DOMNode $Node, $bAutoAtWhenCDATA, $nmCBIconv = null)
    {
        if ($Node instanceof \DOMCdataSection) {
            $sStr = $bAutoAtWhenCDATA ? "@$Node->textContent" : $Node->textContent;
            return $nmCBIconv === null ? $sStr : call_user_func($nmCBIconv, $sStr);
        } elseif ($Node instanceof \DOMText) {
            $sStr = ($bAutoAtWhenCDATA && $Node->textContent[0] === '@') ?
                "\\{$Node->textContent}" : $Node->textContent;
            return $nmCBIconv === null ? $sStr : call_user_func($nmCBIconv, $sStr);
        } else {
            $sIndex = $Node->nodeName;
            $mValue = null;
            if ($Node->hasAttributes()) {
                foreach ($Node->attributes as $sK => $sV) {
                    $sV = (string)$sV->value;
                    if ($nmCBIconv !== null) {
                        $sK = call_user_func($nmCBIconv, $sK);
                        $sV = call_user_func($nmCBIconv, $sV);
                    }
                    $aArr['__attr__'][$sK] = $sV;
                }
            }
            if ($Node->hasChildNodes()) {
                $ChildNodes = $Node->childNodes;
                $iL         = $ChildNodes->length;
                for ($i = 0; $i < $iL; $i++) {
                    $mRS = self::ParseXML($ChildNodes->item($i), $bAutoAtWhenCDATA, $nmCBIconv);
                    if (is_string($mRS)) {
                        if ($iL !== 1) {
                            trigger_error(
                                'XML format strange that a node has text node and others. function will ignore others',
                                E_USER_WARNING
                            );
                        }
                        $mValue = $mRS;
                        break;
                    } else {
                        $mValue[] = $mRS;
                    }
                }
            }
            return array($sIndex => $mValue);
        }
    }

    /**
     * @param array        $aData
     * @param string       $sRoot
     * @param string       $sVersion
     * @param string       $sCharset
     * @param mixed | null $nmCBIconv
     *
     * @return \DOMDocument
     */
    public static function Array2XML(
        array $aData,
        $sRoot = 'root',
        $sVersion = '1.0',
        $sCharset = 'utf-8',
        $nmCBIconv = null
    ) {
        $DOM  = new \DOMDocument($sVersion, $sCharset);
        $Root = $DOM->createElement($sRoot);
        self::BuildXML($aData, $Root, $DOM, $nmCBIconv);
        $DOM->appendChild($Root);
        return $DOM->saveXML();
    }

    /**
     * @param array        $mData
     * @param \DOMNode     $ParentDOM
     * @param \DOMDocument $DOMDocument
     * @param mixed | null $nmCBIconv
     */
    public static function BuildXML($mData, \DOMNode $ParentDOM, \DOMDocument $DOMDocument, $nmCBIconv = null)
    {
        if (is_array($mData)) {
            foreach ($mData as $mK => $mV) {
                if (is_int($mK)) {
                    if (isset($mV['__attr__'])) {
                        $aAttr = $mV['__attr__'];
                        unset($mV['__attr__']);
                    }

                    if (count($mV) !== 1) {
                        trigger_error(
                            'Error format when build array to xml. You must define one and only one k=>v in array value when your current index is int',
                            E_USER_WARNING
                        );
                    }
                    reset($mV);
                    $mK = key($mV);
                    $mV = current($mV);
                }

                $CurDOM = $DOMDocument->createElement($mK);

                if (isset($aAttr) && is_array($aAttr)) {
                    foreach ($aAttr as $sK => $sV) {
                        if ($nmCBIconv !== null) {
                            $sK = call_user_func($nmCBIconv, $sK);
                            $sV = call_user_func($nmCBIconv, $sV);
                        }
                        $DomAttr        = $DOMDocument->createAttribute($sK);
                        $DomAttr->value = (string)$sV;
                        $CurDOM->appendChild($DomAttr);
                    }
                }

                self::BuildXML($mV, $CurDOM, $DOMDocument, $nmCBIconv);
                $ParentDOM->appendChild($CurDOM);
            }
        } else {
            $bCreateCDATA = false;
            $sStr         = (string)$mData;

            if ($sStr === '@') {
                $bCreateCDATA = true;
                $sStr         = substr($sStr, 1);
            } elseif ("{$sStr[0]}{$sStr[1]}" === '\@') {
                $sStr = substr($mData, 1);
            }

            if ($nmCBIconv !== null) {
                $sStr = call_user_func($nmCBIconv, $sStr);
            }

            $ParentDOM->appendChild(
                $bCreateCDATA ?
                    $DOMDocument->createCDATASection($sStr) :
                    $DOMDocument->createTextNode($sStr)
            );
        }
    }
}
