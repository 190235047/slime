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
    $N1 = new Node('music');
    $N2 = new Node('video');
    $N3 = new Node('book');

    $N1_1 = new Node('R&B');
    $N2_1->appendTo($N1);

    $N1->appendTo($Root);
    $N2->appendTo($Root);
    $N3->appendTo($Root);

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

    /**
     * @param mixed       $mValue
     * @param null | Node $Parent
     */
    public function __construct($mValue, $Parent = null)
    {
        $this->mValue = $mValue;
        $this->Parent = $Parent;
    }

    private function _addChild(Node $ChildNode)
    {
        $this->aChildren[] = $ChildNode;
    }

    /**
     * @param Node $ParentNode
     */
    public function appendTo(Node $ParentNode)
    {
        $this->Parent = $ParentNode;
        $ParentNode->_addChild($this);
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->mValue;
    }

    /**
     * @return Node[]
     */
    public function getChildren()
    {
        return $this->aChildren;
    }

    /**
     * @param int $i
     *
     * @return null|Node
     */
    public function getChild($i)
    {
        return isset($this->aChildren[$i]) ? $this->aChildren[$i] : null;
    }

    /**
     * @return Node[]
     */
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

    /**
     * @return null|Node
     */
    public function getParent()
    {
        return $this->Parent;
    }

    /**
     * @param int $iMax
     *
     * @return null|Node
     */
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

    /**
     * @return int
     */
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
