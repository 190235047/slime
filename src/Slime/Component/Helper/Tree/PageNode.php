<?php
namespace Slime\Component\Helper;

/**
 * Class PageNode
 *
 * @package Slime\Component\Helper
 * @author  smallslime@gmail.com
 */
class Tree_PageNode extends Tree_Node
{
    /**
     * @var Tree_PageNode[]
     */
    public $aChildren = array();

    public function __construct(
        Tree_PagePool $Pool,
        $sKey,
        $aAttr = array(),
        Tree_PageNode $Parent = null
    ) {
        $this->sKey   = $sKey;
        $this->Pool   = $Pool;
        $this->Parent = $Parent;
        $this->aAttr  = $aAttr;
        $this->iLevel = $Parent === null ? 0 : $Parent->iLevel + 1;
        if (!isset($this->aAttr['url'])) {
            $this->aAttr['url'] = $sKey;
        }
    }

    public function buildA($sAttr = '', $CBUrl = null)
    {
        $sName = $this->getAttr('name');
        $sUrl  = $this->getAttr('url');
        if ($CBUrl) {
            $sUrl = call_user_func($CBUrl, $sUrl);
        }
        return sprintf('<a href="%s" title="%s" %s>%s</a>', $sUrl, $sName, $sAttr, $sName);
    }

    public function buildBreadNav($aAttach = array(), $aAttr = '', $CB = null)
    {
        $sBefore  = isset($aAttach['before']) ? $aAttach['before'] : '';
        $sAfter   = isset($aAttach['after']) ? $aAttach['after'] : '';
        $sResult  = '';
        $PageNode = $this;
        do {
            if (!$sResult) {
                $sResult = '<span>' . $this->getAttr('name') . '<span>';
            } else {
                $sResult = $sBefore . $PageNode->buildA($aAttr, $CB) . $sAfter . $sResult;
            }
            $PageNode = $PageNode->Parent;
        } while ($PageNode);

        return $sResult;
    }
}