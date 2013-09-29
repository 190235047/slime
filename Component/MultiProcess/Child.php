<?php
namespace SlimeFramework\Component\MultiProcess;

use Psr\Log\LoggerInterface;

/**
 * 创建一个Child对象, 对象在初始化时, 会fork一个子进程. 至此child的行为在父子进程中会有不同. 而通过这个对象, 可以实现父子进程通信:
 * 在父进程中 :
 *     1. 构造函数中: 以写方式打开父到子管道, 以读方式打开子到父管道
 *     2. Method: sendMessage(发送消息给子进程)
 *     3. Method: receiveMessage(接受子进程消息, 注意这个是异步方法, 若无消息返回false)
 * 在子进程中:
 *     1. 构造函数中: 以写方式打开子到父管道, 以读方式打开父到子管道
 *     2. 进入主Loop循环, 不断尝试读取主进程发来的消息
 *     3. 获取到消息后, 创建Job对象, 运行.
 *     4. 将结果(0:成功;else:失败)通过子到父的写管道写回主进程
 *
 * @package SlimeFramework\Component\MultiProcess
 * @author  smallslime@gmail.com
 */
class Child
{
    public function __construct($sPipeDir, $sJobClass, LoggerInterface $Log)
    {
        $this->sJobClass = $sJobClass;
        $this->Log       = $Log;
        $sPipeDir        = rtrim($sPipeDir, '/');

        $iPID = pcntl_fork();
        if ($iPID < 0) {
            trigger_error('fork error', E_USER_ERROR);
            exit(1);
        } elseif ($iPID) {
            # father
            $this->iPID = $iPID;

            $this->sFifoF2C = $sPipeDir . '/sf_mp_f2c_' . $this->iPID;
            $this->sFifoC2F = $sPipeDir . '/sf_mp_c2f_' . $this->iPID;
            posix_mkfifo($this->sFifoF2C, 0600);
            $this->rFifoF2C = fopen($this->sFifoF2C, 'w');
            stream_set_blocking($this->rFifoF2C, 1);
            $this->rFifoC2F = fopen($this->sFifoC2F, 'r');
            stream_set_blocking($this->rFifoC2F, 0);
        } else {
            # child
            $this->iPID = posix_getpid();
            if ($this->Log instanceof \SlimeFramework\Component\Log\Logger) {
                $this->Log->sGUID = "CHILD:$this->iPID";
            }

            $this->Log->debug('child start');

            $this->sFifoF2C = $sPipeDir . '/sf_mp_f2c_' . $this->iPID;
            $this->sFifoC2F = $sPipeDir . '/sf_mp_c2f_' . $this->iPID;
            posix_mkfifo($this->sFifoC2F, 0600);
            $this->rFifoF2C = fopen($this->sFifoF2C, 'r');
            stream_set_blocking($this->rFifoF2C, 1);
            $this->rFifoC2F = fopen($this->sFifoC2F, 'w');
            stream_set_blocking($this->rFifoC2F, 1);

            $this->Log->debug('child ready');

            $this->_receive();
        }
    }

    /**
     * father send message
     *
     * @param $sMessage
     */
    public function sendMessage($sMessage)
    {
        fwrite($this->rFifoF2C, $sMessage . "\n");
    }

    /**
     * father receive message
     *
     * @return string
     */
    public function receiveMessage()
    {
        return fgets($this->rFifoC2F);
    }

    /**
     * child main loop
     */
    private function _receive()
    {
        while (true) {
            /** @var Task $Job */
            $Job     = new $this->sJobClass(fgets($this->rFifoF2C), $this->Log);
            $bResult = $Job->run();
            unset($Job);
            fwrite($this->rFifoC2F, ($bResult ? '0' : '1') . "\n");
        }
    }
}

