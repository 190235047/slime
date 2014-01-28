<?php
namespace Slime\Component\Helper;

/**
 * Class Arr2XML
 *
 * @package Slime\Component\Helper
 * @author  smallslime@gmail.com
 */
class Arr2XML
{
    /** @var \DOMDocument */
    protected $DOMDocument;

    public function __construct($sVersion = '1.0', $sCharset = 'utf-8', callable $mCBKeySet = null)
    {
        $this->sVersion  = $sVersion;
        $this->sCharset  = $sCharset;
        $this->mCBKeySet = $mCBKeySet;
    }

    /**
     * @param string   $sVersion
     * @param string   $sCharset
     * @param callable $mCBKeySet
     *
     * @return Arr2XML
     */
    public static function factory($sVersion = '1.0', $sCharset = 'utf-8', callable $mCBKeySet = null)
    {
        return new self($sVersion, $sCharset, $mCBKeySet);
    }

    public function Array2XML($mData, $sRoot = 'root')
    {
        $this->DOMDocument = new \DOMDocument($this->sVersion, $this->sCharset);
        if (!empty($sRoot)) {
            $EL = $this->DOMDocument->createElement($sRoot);
            $this->DOMDocument->appendChild($EL);
        } else {
            $EL = $this->DOMDocument;
        }
        $this->_Array2XML($mData, $EL);
        return $this->DOMDocument->saveXML();
    }

    private function _Array2XML($mData, \DOMNode $DOM, $sPreKey = '')
    {
        foreach ($mData as $mK => $mV) {
            $sK = is_int($mK) ?
                ($this->mCBKeySet===null ? $sPreKey : call_user_func($this->mCBKeySet, $sPreKey)) :
                (string)$mK;

            if (is_array($mV)) {
                $EL = $this->DOMDocument->createElement($sK);
                $this->_Array2XML($mV, $EL, $sK);
            } else {
                $EL = $this->DOMDocument->createElement($sK);
                $EL->appendChild($this->DOMDocument->createTextNode((string)$mV));
            }
            $DOM->appendChild($EL);
        }
    }
}