<?php
namespace Slime\Component\MultiProcessJob;

use Psr\Log\LoggerInterface;

/**
 * Interface ITask
 *
 * @package Slime\Component\MultiProcessJob
 * @author  smallslime@gmail.com
 */
interface ITask
{
    /**
     * @param LoggerInterface $Logger
     *
     * @return string|bool string as ok and false as fail
     */
    public function fetchMsgInMain(LoggerInterface $Logger);

    /**
     * @param string          $sMessage
     * @param LoggerInterface $Logger
     *
     * @return void
     */
    public function dealMsgInChild($sMessage, LoggerInterface $Logger);

    /**
     * @param string          $sMessage
     * @param LoggerInterface $Logger
     *
     * @return void
     */
    public function dealWhenException($sMessage, LoggerInterface $Logger);
}