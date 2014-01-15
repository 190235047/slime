<?php
namespace Slime\Component\MultiProcessJob;

use Slime\Component\Log\Logger;

/**
 * Interface ITask
 *
 * @package Slime\Component\MultiProcessJob
 * @author  smallslime@gmail.com
 */
interface ITask
{
    /**
     * @param Logger $Logger
     *
     * @return string|bool string as ok and false as fail
     */
    public function fetchMsgInMain(Logger $Logger);

    /**
     * @param string          $sMessage
     * @param Logger $Logger
     *
     * @return void
     */
    public function dealMsgInChild($sMessage, Logger $Logger);

    /**
     * @param string          $sMessage
     * @param Logger $Logger
     *
     * @return void
     */
    public function dealWhenException($sMessage, Logger $Logger);
}