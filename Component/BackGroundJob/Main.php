<?php
namespace SlimeFramework\Component\BackGroundJob;

use SlimeFramework\Component\Log\Logger;
use SlimeFramework\Component\MultiProcess\MPPoolModel;

class Main extends MPPoolModel
{
    /** @var IJobQueue */
    private $JobQueue;

    public function setJobQueue(IJobQueue $JobQueue)
    {
        $this->JobQueue = $JobQueue;
    }

    /**
     * @return string|null
     */
    protected function getMessage()
    {
        return $this->JobQueue->pop();
    }
}