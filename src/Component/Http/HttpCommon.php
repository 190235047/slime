<?php
namespace Slime\Component\Http;

/**
 * Class HttpCommon
 *
 * @package Slime\Component\Http
 * @author  smallslime@gmail.com
 */
abstract class HttpCommon
{
    /**
     * @var Bag_Header
     */
    protected $Header;
    protected $sContent;

    public function getHeader($sKey)
    {
        return $this->Header[$sKey];
    }

    public function setHeader($mKeyOrKVMap, $sValue = null, $bOverwrite = true)
    {
        if (is_array($mKeyOrKVMap)) {
            $this->Header->merge($mKeyOrKVMap, $bOverwrite);
        } else {
            $this->Header[$mKeyOrKVMap] = $sValue;
        }

        return $this;
    }

    public function getContent()
    {
        return $this->sContent;
    }

    public function setContent($sContent)
    {
        $this->sContent = $sContent;
        return $this;
    }
}