<?php
namespace SlimeFramework\Component\Http;

class HttpCommon
{
    protected $sRequestMethod;
    protected $Header;
    protected $sContent;

    public function getRequestMethod()
    {
        return $this->sRequestMethod;
    }

    public function setRequestMethod($sRequestMethod)
    {
        $this->sRequestMethod = $sRequestMethod;
        return $this;
    }

    public function getHeader($sKey)
    {
        return $this->Header[$sKey];
    }

    public function setHeader($mKeyOrKVMap, $sValue = null)
    {
        if (is_array($mKeyOrKVMap)) {
            foreach ($mKeyOrKVMap as $sK => $sV) {
                if ($sV === null) {
                    unset($this->Header[$sK]);
                } else {
                    $this->Header[$sK] = $sV;
                }
            }
        } else {
            if ($sValue===null) {
                unset($this->Header[$mKeyOrKVMap]);
            } else {
                $this->Header[$mKeyOrKVMap] = $sValue;
            }
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