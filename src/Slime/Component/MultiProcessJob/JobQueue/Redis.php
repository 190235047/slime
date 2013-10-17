<?php
namespace Slime\Component\MultiProcessJob;

/**
 * Class JobQueue_Redis
 *
 * @package Slime\Component\BackGroundJob
 */
class JobQueue_Redis implements IJobQueue
{
    /**
     * @param \Redis $Redis      Redis instance
     * @param string $sQueueName queue name
     */
    public function __construct(\Redis $Redis, $sQueueName)
    {
        $this->Redis      = $Redis;
        $this->sQueueName = $sQueueName;
    }

    /**
     * Pop an job from queue
     *
     * @param int    $iErr
     * @param string $sErr
     *
     * @return string|bool
     */
    public function pop(&$iErr = 0, &$sErr = '')
    {
        $mRS = $this->Redis->rPop($this->sQueueName);
        if ($mRS === false) {
            $iErr = 1;
            $sErr = 'UnSerialize failed';
            $mRS = false;
        }
        return $mRS;
    }

    /**
     * Push an job into queue
     *
     * @param string $sJob
     * @param int    $iErr
     * @param string $sErr
     *
     * @return void
     */
    public function push($sJob, &$iErr = 0, &$sErr = '')
    {
        $bRS = $this->Redis->lPush($this->sQueueName, $sJob);
        if ($bRS === false) {
            $iErr = 1;
        }
    }
}