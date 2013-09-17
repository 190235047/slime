<?php
use SlimeFramework\Component\BackGroundJob;
use SlimeFramework\Component\Log;

$Daemon = new BackGroundJob\Main(
    10,
    '/tmp/fifo',
    '\\Slime\\BackGroundJob\\Job',
    1000,
    new Log\Logger(array(new Log\Writer_STDFD()), Log\Logger::LEVEL_ALL)
);

$JobQueue = new BackGroundJob\JobQueue_SysMsg();
$Daemon->setJobQueue($JobQueue);

$Daemon->run();