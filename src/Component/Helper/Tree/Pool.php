<?php
namespace Slime\Component\Context\Tree;

/**
 * Class Pool
 *
 * @package Slime\Component\Helper
 * @author  smallslime@gmail.com
 */
class Pool
{
    /**
     * @var Node[]
     */
    public $aPool = array();

    public $aaPoolLevel = array();

    /**
     * @param array $aArr
     *
     * @return Node
     * @throws \InvalidArgumentException
     */
    public static function initFromArrayRecursion(array $aArr)
    {
        if (count($aArr) !== 1) {
            throw new \InvalidArgumentException('[TREE] : Tree array data must own and only can own one root node');
        }
        $sKey  = key($aArr);
        $aData = current($aArr);
        if (empty($aData[0])) {
            $RootNode = new Node(new self(), $sKey, $aData);
        } else {
            $aChildren = $aData[0];
            unset($aData[0]);
            $RootNode = new Node(new self(), $sKey, $aData);
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

    /**
     * @param Node $Node
     */
    public function addNode($Node)
    {
        $sKey = $Node->sKey;
        if (isset($this->aPool[$sKey])) {
            trigger_error("Pool has node with key[{$sKey}]", E_USER_WARNING);
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
     * @param string $sKey
     *
     * @return Node|null
     */
    public function findNode($sKey)
    {
        if (!isset($this->aPool[$sKey])) {
            trigger_error("There is no key[{$sKey}] in pool", E_USER_WARNING);
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
