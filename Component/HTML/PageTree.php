<?php
namespace SlimeFramework\Component\HTML;

class PageTree
{
    private $aPageStorage = array();

    public function addPageBean(PageBean $PageBean)
    {
        $sKey = $PageBean->sKey;
        if (isset($this->aPageStorage[$sKey])) {
            throw new \RuntimeException(sprintf("sKey[%s] has been exist", $sKey));
        }
        $this->aPageStorage[$sKey] = $PageBean;
    }

    /**
     * @param $sKey
     * @param bool $bExceptionIfNotFound
     * @return PageBean
     * @throws \InvalidArgumentException
     */
    public function find($sKey, $bExceptionIfNotFound = true)
    {
        if (!isset($this->aPageStorage[$sKey])) {
            if ($bExceptionIfNotFound) {
                throw new \InvalidArgumentException("sKey[$sKey] is not found");
            } else {
                return null;
            }
        }
        return $this->aPageStorage[$sKey];
    }
}

class PageBean
{
    public $sUrl;
    public $sName;
    public $sKey;

    /**
     * @var PageBean[]
     */
    public $aChildren = array();

    /**
     * @var PageTree
     */
    public $PageTree;

    /**
     * @var PageBean
     */
    public $Parent;

    public function __construct($sUrl, $sName, PageTree $PageTree, $Parent = null, $sKey = null)
    {
        $this->sUrl  = $sUrl;
        $this->sName = $sName;
        $this->sKey  = $sKey ? $sKey : $this->sUrl;

        $this->PageTree = $PageTree;
        $this->Parent   = $Parent;
    }

    public function addChild($sUrl, $sName, $sKey = null)
    {
        if ($sKey === null) {
            $sKey = $sUrl;
        }

        $PageBean = new self($sUrl, $sName, $this->PageTree, $this, $sKey);
        $this->PageTree->addPageBean($PageBean);
        $this->aChildren[$sKey] = $PageBean;
        return $PageBean;
    }

    public function buildA($sAttr = '')
    {
        return sprintf('<a href="%s" title="%s" %s>%s</a>', $this->sUrl, $this->sName, $sAttr, $this->sName);
    }

    public function buildBreadNav()
    {
        $sResult  = '';
        $PageBean = $this;
        do {
            if (!$sResult) {
                $sResult = '<span>' . $this->sName . '<span>';
            } else {
                $sResult = $PageBean->buildA() . $sResult;
            }
            $PageBean = $PageBean->Parent;
        } while ($PageBean);
        return $sResult;
    }
}