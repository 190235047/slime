<?php
namespace SlimeFramework\Component\BackGroundJob;

use SlimeFramework\Component\MultiProcess\ModelPool;

class Main extends ModelPool
{
    /** @var IJobQueue */
    private $JobQueue;

    public function setJobQueue(IJobQueue $JobQueue)
    {
        $this->JobQueue = $JobQueue;
    }

    /**
     * @return string
     */
    protected function getMessage()
    {
        return $this->JobQueue->pop();
    }
}