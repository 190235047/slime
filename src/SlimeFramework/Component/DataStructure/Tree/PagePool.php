<?php
namespace SlimeFramework\Component\DataStructure\Tree;

use Psr\Log\LoggerInterface;

class Tree_PagePool extends Tree_Pool
{
    public static function initFromArrayRecursion($aArr, LoggerInterface $Log)
    {
        if (count($aArr) !== 1) {
            $Log->error('Tree array data must own and only can own one root node');
        }
        $sKey  = key($aArr);
        $aData = current($aArr);
        if (empty($aData[0])) {
            $RootNode = new PageNode(new self($Log), $sKey, $aData);
        } else {
            $aChildren = $aData[0];
            unset($aData[0]);
            $Pool     = new self($Log);
            $RootNode = new PageNode($Pool, $sKey, $aData);
            $Pool->addNode($RootNode);
            self::_initFromArrayRecursion($aChildren, $RootNode);
        }
        return $RootNode;
    }
}
