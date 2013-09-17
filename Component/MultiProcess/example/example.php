<?php
require '../MPPoolModel.php';
require '../Pool.php';
require '../Child.php';
require '../Task.php';
require '../../Log/Logger.php';
require '../../Log/IWriter.php';
require '../../Log/Writer/STDFD.php';

class MyMP extends \Slime\MultiProcess\MPPoolModel
{
    /**
     * @return string|null
     */
    protected function getMessage()
    {
        $i = rand(1, 100);
        if ($i<=50) {
            return null;
        }
        return json_encode(array(rand(1, 100), rand(1, 100)));
    }
}

class MyJob extends \Slime\MultiProcess\Job
{
    public function run()
    {
        $aArr = json_decode($this->sMessage);
        $this->Logger->debug('job run:' . ($aArr[0] + $aArr[1]));
        return true;
    }
}

$MP = new MyMP(10, 'fifo', 'MyJob', 1000, new \Slime\Log\Logger(array(new \Slime\Log\Writer_STDFD())));
$MP->run();