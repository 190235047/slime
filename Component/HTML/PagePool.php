<?php
namespace SlimeFramework\Component\HTML;

use Psr\Log\LoggerInterface;

class PagePool
{
    private $aPageStorage = array();

    public function __construct(LoggerInterface $Log)
    {
        $this->Log = $Log;
    }

    public function addPageBean(PageBean $PageBean)
    {
        $sKey = $PageBean->sKey;
        if (isset($this->aPageStorage[$sKey])) {
            $this->Log->warning('Pool has PageBean with key[{key}]', array('key' => $sKey));
        }
        $this->aPageStorage[$sKey] = $PageBean;
    }

    /**
     * @param $sKey
     * @return PageBean|null
     */
    public function find($sKey)
    {
        if (!isset($this->aPageStorage[$sKey])) {
            $this->Log->warning('There is no key[{key}] in pool', array('key' => $sKey));
            return null;
        }
        return $this->aPageStorage[$sKey];
    }
}

class PageBean
{
    /**
     * @var PageBean[]
     */
    public $aChildren = array();

    /**
     * @var PageBean[]
     */
    public $aChildrenAll = array();

    public function __construct($sUrl, $sName, PagePool $PageTree, $Parent = null, $sKey = null, $bDisplay = true)
    {
        $this->sUrl     = $sUrl;
        $this->sName    = $sName;
        $this->sKey     = $sKey ? $sKey : $this->sUrl;
        $this->PageTree = $PageTree;
        $this->Parent   = $Parent;
        $this->iLevel   = 0;
        $this->bDisplay = $bDisplay;
    }

    /**
     * @param bool $bIncHidden
     * @return PageBean[]
     */
    public function getChildren($bIncHidden = false)
    {
        return $bIncHidden ? $this->aChildrenAll : $this->aChildren;
    }

    /**
     * @param int $iUntilLevel top is 0
     * @return PageBean|null
     */
    public function findParent($iUntilLevel)
    {
        $Bean = $this;
        while ($Bean->Parent !== null) {
            if ($iUntilLevel === $Bean->iLevel) {
                break;
            }
            $Bean = $Bean->Parent;
        }

        return $Bean->iLevel === $iUntilLevel ? $Bean : null;
    }

    public function addChild($sUrl, $sName, $sKey = null, $bDisplay = true)
    {
        if ($sKey === null) {
            $sKey = $sUrl;
        }

        $PageBean         = new self($sUrl, $sName, $this->PageTree, $this, $sKey, $bDisplay);
        $PageBean->iLevel = $this->iLevel + 1;
        $this->PageTree->addPageBean($PageBean);
        $this->aChildrenAll[$sKey] = $PageBean;
        if ($PageBean->bDisplay) {
            $this->aChildren[$sKey] = $PageBean;
        }
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