<?php
namespace SlimeFramework\Component\BackGroundJob;

class JobQueue_SysMsg implements IJobQueue
{
    public function __construct($sProjectWithOneChar = 'A')
    {
        $this->MSGQueue = msg_get_queue(ftok(__FILE__, $sProjectWithOneChar), 0666);
    }

    /**
     * Pop an job from queue
     *
     * @param int    $iErr
     * @param string $sErr
     *
     * @return string
     */
    public function pop(&$iErr = 0, &$sErr = '')
    {
        msg_receive($this->MSGQueue, 1, $iMSGType, 1024, $sMessage, true, MSG_IPC_NOWAIT, $iErrorCode);
        if (!$iErrorCode) {
            $iErr = 1;
            $sErr = sprintf('[msg_receive][%d]', $iErrorCode);
        }

        return (string)$sMessage;
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
        $bResult = msg_send($this->MSGQueue, 1, $sJob, true, false, $iErrorCode);
        if (!$bResult) {
            $iErr = 1;
            $sErr = sprintf('Error to push msg[%s] into msgqueue[%d]', $sJob, $iErrorCode);
        }
    }
}
