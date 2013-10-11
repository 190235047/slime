# MultiProcess Job Deal

* Example In Test/Test.php

```php
use Psr\Log\LoggerInterface;
use Slime\Component\MultiProcess;
use Slime\Component\Log;

class Task implements MultiProcess\ITask
{
    /**
     * @param LoggerInterface $Logger
     *
     * @return string|bool string as ok and false as fail
     */
    public function fetchMsgInMain(LoggerInterface $Logger)
    {
        if (($i = rand(0, 100))>50) {
            return json_encode(array($i, rand(100, 1000)));
        } else {
            return false;
        }
    }

    /**
     * @param string          $sMessage
     * @param LoggerInterface $Logger
     *
     * @return void
     */
    public function dealMsgInChild($sMessage, LoggerInterface $Logger)
    {
        $aArr = json_decode($sMessage);
        if (count($aArr) == 2) {
            $Logger->info('{a} + {b} = {c}', array('a' => $aArr[0], 'b' => $aArr[1], 'c' => $aArr[0] + $aArr[1]));
        } else {
            $Logger->warning('Message[{msg}] format error', array('msg' => $sMessage));
        }
    }

    /**
     * @param string          $sMessage
     * @param LoggerInterface $Logger
     *
     * @return void
     */
    public function dealWhenException($sMessage, LoggerInterface $Logger)
    {
        $Logger->warning('Error occur with message[{msg}]', array('msg' => $sMessage));
    }
}

$PoolManager = new MultiProcess\PoolManager(
    '/tmp/fifo',
    new Log\Logger(array(new Log\Writer_STDFD())),
    new Task()
);
$PoolManager->run();
```