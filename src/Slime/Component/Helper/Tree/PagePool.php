<?php
namespace Slime\Component\Helper;

/**
 * Class PagePool
 *
 * @package Slime\Component\Helper
 * @author  smallslime@gmail.com
 */
class Tree_PagePool extends Tree_Pool
{
    public static function initFromArrayRecursion($aArr)
    {
        if (count($aArr) !== 1) {
            throw new \Exception('Tree array data must own and only can own one root node');
        }
        $sKey  = key($aArr);
        $aData = current($aArr);
        if (empty($aData[0])) {
            $RootNode = new Tree_PageNode(new self(), $sKey, $aData);
        } else {
            $aChildren = $aData[0];
            unset($aData[0]);
            $Pool     = new self();
            $RootNode = new Tree_PageNode($Pool, $sKey, $aData);
            $Pool->addNode($RootNode);
            self::_initFromArrayRecursion($aChildren, $RootNode);
        }
        return $RootNode;
    }
}
