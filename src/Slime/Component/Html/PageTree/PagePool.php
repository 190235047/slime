<?php
namespace Slime\Component\Html\PageTree;

use Slime\Component\DataStructure\Tree;

/**
 * Class PagePool
 *
 * @package Slime\Component\Html\PageTree
 * @author  smallslime@gmail.com
 */
class PagePool extends Tree\Pool
{
    public static function initFromArrayRecursion($aArr)
    {
        if (count($aArr) !== 1) {
            throw new \Exception('Tree array data must own and only can own one root node');
        }
        $sKey  = key($aArr);
        $aData = current($aArr);
        if (empty($aData[0])) {
            $RootNode = new PageNode(new self(), $sKey, $aData);
        } else {
            $aChildren = $aData[0];
            unset($aData[0]);
            $Pool     = new self();
            $RootNode = new PageNode($Pool, $sKey, $aData);
            $Pool->addNode($RootNode);
            self::_initFromArrayRecursion($aChildren, $RootNode);
        }
        return $RootNode;
    }
}
