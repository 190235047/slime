<?php
namespace Slime\Component\Tree;

/**
 * Class Node
 *
 * @package Slime\Component\Tree
 * @author  smallslime@gmaile.com
 *
 * @example
 *
    $Root = new Node('root');
    $L1_L1 = new Node('left_1');
    $L1_L2 = new Node('left_2');
    $L1_R1 = new Node('right_1');

    $L2_L2_A = new Node('l2l2A');
    $L2_L2_A->addAsChild($L1_L2);

    $L1_L1->addAsChild($Root);
    $L1_L2->addAsChild($Root);
    $L1_R1->addAsChild($Root);

    foreach ($Root as $iLevel => $mV) {
        var_dump($iLevel, $mV);
    }
 */
class Node implements \IteratorAggregate
{
    /**
     * @var Node[]
     */
    public $aChildren = array();

    /** @var null | Node */
    protected $Parent;

    protected $mValue;

    public function __construct($mValue, $Parent = null) {
        $this->mValue = $mValue;
        $this->Parent = $Parent;
    }

    private function _addChild(Node $ChildNode)
    {
        $this->aChildren[] = $ChildNode;
    }

    public function addAsChild(Node $ParentNode)
    {
        $this->Parent = $ParentNode;
        $ParentNode->_addChild($this);
    }

    public function getValue()
    {
        return $this->mValue;
    }

    public function getChildren()
    {
        return $this->aChildren;
    }

    public function getChild($i)
    {
        return isset($this->aChildren[$i]) ? $this->aChildren[$i] : null;
    }

    public function getBrother()
    {
        $aArr = $this->getParent()->getChildren();
        foreach ($aArr as $iK => $Item) {
            if ($Item===$this) {
                unset($aArr[$iK]);
            }
        }
        return $aArr;
    }

    public function getParent()
    {
        return $this->Parent;
    }

    public function getForbear($iMax = 0)
    {
        if ($iMax <= 0) {
            $iMax = -1;
            $i    = -2;
        } else {
            $i = 0;
        }
        $P = $this;
        while ($i < $iMax) {
            $PP = $P->getParent();
            if ($i >= 0) {
                $i++;
            }
            if ($P===null) {
                break;
            } else {
                $P = $PP;
            }
        }

        return $P;
    }

    public function getHeight()
    {
        $i = -1;
        do {
            $P = $this->getParent();
            $i++;
        } while ($P===null);
        return $i;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return \Traversable An instance of an object implementing <b>Iterator</b> or
     *       <b>Traversable</b>
     */
    public function getIterator()
    {
        return new DeepIterator($this);
    }
}
