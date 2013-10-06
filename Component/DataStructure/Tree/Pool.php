<?php
namespace SlimeFramework\Component\DataStructure\Tree;

use Psr\Log\LoggerInterface;

class Tree_Pool
{
    /**
     * @var Node[]
     */
    public $aPool = array();

    public $aaPoolLevel = array();

    public static function initFromArrayRecursion($aArr, LoggerInterface $Log)
    {
        if (count($aArr) !== 1) {
            $Log->error('Tree array data must own and only can own one root node');
        }
        $sKey  = key($aArr);
        $aData = current($aArr);
        if (empty($aData[0])) {
            $RootNode = new Node(new self($Log), $sKey, $aData);
        } else {
            $aChildren = $aData[0];
            unset($aData[0]);
            $RootNode = new Node(new self($Log), $sKey, $aData);
            self::_initFromArrayRecursion($aChildren, $RootNode);
        }
        return $RootNode;
    }

    protected static function _initFromArrayRecursion($aArr, Node $Parent)
    {
        foreach ($aArr as $sK => $aData) {
            if (isset($aData[0])) {
                $aChildren = $aData[0];
                unset($aData[0]);
            } else {
                $aChildren = null;
            }

            $Node = $Parent->bornChild($sK, $aData);

            if (!empty($aChildren)) {
                self::_initFromArrayRecursion($aChildren, $Node);
            }
        }
    }

    public function __construct(LoggerInterface $Log)
    {
        $this->Log = $Log;
    }

    public function addNode(Node $Node)
    {
        $sKey = $Node->sKey;
        if (isset($this->aPool[$sKey])) {
            $this->Log->warning('Pool has node with key[{key}]', array('key' => $sKey));
        } else {
            $this->aPool[$sKey]                      = $Node;
            $this->aaPoolLevel[$Node->iLevel][$sKey] = $Node;
        }
    }

    /**
     * @param $iLevel
     *
     * @return Node[]
     */
    public function findNodesByLevel($iLevel)
    {
        return $this->aaPoolLevel[$iLevel];
    }

    /**
     * @param $sKey
     *
     * @return Node|null
     */
    public function findNode($sKey)
    {
        if (!isset($this->aPool[$sKey])) {
            $this->Log->warning('There is no key[{key}] in pool', array('key' => $sKey));
            return null;
        }
        return $this->aPool[$sKey];
    }

    /**
     * @param string $sKey
     *
     * @return bool
     */
    public function deleteNode($sKey)
    {
        if (isset($this->aPool[$sKey])) {
            unset($this->aPool[$sKey]);
            return true;
        }
        return false;
    }
}
