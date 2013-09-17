# PHP BackGroundJob
## Depends on SlimeFramework\MultiProcess
* /your_path_of_php_bin /your_daemon_file 2>/tmp/sf_bgjob_err 1>/tmp/sf_bgjob_log &
* Master process init sub process pool
* Master process pop message from one JobQueue(implements \SlimeFramework\BackGroundJob\IJobQueue)
* Master process find a idle sub process, mark it as busy and send it job by using fifo
* Sub process do the job and response to master process by using fifo
* Master process receive response by using fifo, mark sub process as idle
* This is an example

#### Task
`
<?php
namespace SlimeFramework\Component\BackGroundJob;

use SlimeFramework\Component\Log;
use SlimeFramework\Component\MultiProcess\Task;

class MyTask extends Task
{
    public function run()
    {
        $aMessage = json_decode($this->sMessage, true);

        if ($aMessage===false) {
            $this->Logger->warning('message[{msg}] format is error', array('msg' => $this->sMessage));
        }

        if (isset($aMessage['__sf_bgjob_retry__']) && $aMessage['__sf_bgjob_retry__'] >= 3) {
            return true;
        }

        $sFile = $aMessage['file'];
        $CB = $aMessage['cb'];
        $aParam = $aMessage['param'];

        require_once $sFile;
        $bRS = call_user_func($CB, $aParam);
        if ($bRS!==true) {
            $aMessage['__sf_bgjob_retry__'] = 1;
            $bRS = false;
        }
        return $bRS;
    }
}
`

#### Daemon
`
<?php
namespace SlimeFramework\Component\BackGroundJob;

use SlimeFramework\Component\BackGroundJob;
use SlimeFramework\Component\Log;
$Daemon = new BackGroundJob\Main(
    10,
    '/tmp/fifo',
    '\\SlimeFramework\\Component\\BackGroundJob\\MyTask',
    1000,
    new Log\Logger(array(new Log\Writer_STDFD()), Log\Logger::LEVEL_ALL)
);
$JobQueue = new BackGroundJob\JobQueue_SysMsg();
$Daemon->setJobQueue($JobQueue);
$Daemon->run();
`

#### WebLogic
`
<?php
namespace YouApp;
use SlimeFramework\Component\Log;

class Logic_Test
{
    protected $JobQueue;

    public function __construct()
    {
        $this->JobQueue = new BackGroundJob\JobQueue_SysMsg();
    }

    public function actionDo()
    {
        $this->JobQueue->push(
            json_encode(
                array(
                    'file' => __FILE__,
                    'cb' => array(__CLASS__, 'bgDo'),
                    'param' => array(rand(1,100), rand(100,500))
                )
            )
        );
    }

    public static function bgDo($iA, $iB, Log\Logger $Logger)
    {
        $Logger->debug('{a}+{b}={c}', array($iA, $iB, $iA+$iB));
        return true;
    }
}
`