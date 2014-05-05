<?php
namespace Slime\Component\Tree;

class DeepIterator implements \Iterator
{
    protected $aMap = array();
    protected $iCurHeight = 0;

    public function __construct(Node $TreeNode)
    {
        $PreNode = new Node(true);
        $TreeNode->appendTo($PreNode);

        $this->BaseNode    = $TreeNode;
        $this->CurrentNode = $TreeNode;
        $this->PreNode     = $PreNode;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     *
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return $this->CurrentNode->getValue();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        /** @var Node $P */
        $P = $this->CurrentNode;
        do {
            if ($P===$this->PreNode) {
                $this->CurrentNode = $P;
                break;
            }

            $sPHash = spl_object_hash($P);
            $iIndex = $this->aMap[$sPHash] + 1;
            if (($CurNode = $P->getChild($iIndex)) !== null) {
                $this->iCurHeight++;
                $this->CurrentNode   = $CurNode;
                $this->aMap[$sPHash] = $iIndex;
                break;
            }

            $this->iCurHeight--;
            $P = $P->getParent();
        } while (true);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     *
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->iCurHeight;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     *
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     *       Returns true on success or false on failure.
     */
    public function valid()
    {
        $this->aMap[spl_object_hash($this->CurrentNode)] = -1;
        return $this->CurrentNode !== $this->PreNode;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->CurrentNode = $this->BaseNode;
        $this->aMap        = array(spl_object_hash($this->PreNode) => -1);
    }
}
