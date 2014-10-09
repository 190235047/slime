<?php
namespace Slime\Component\Context;

/**
 * Class Event
 *
 * @package Slime\Component\Helper
 * @author  smallslime@gmail.com
 */
class Event
{
    /**
     * @param string $sEventName
     */
    public static function occurEvent($sEventName)
    {
        $Event = Context::getInst()->get('Event');
        if ($Event instanceof Event) {
            $Event->occur($sEventName, func_num_args() > 1 ? array_slice(func_get_args(), 1) : array());
        }
    }

    /**
     * @param string $sEventName
     * @param mixed  $mCB
     */
    public static function regEvent($sEventName, $mCB)
    {
        $Event = Context::getInst()->get('Event');
        if ($Event instanceof Event) {
            $Event->register($sEventName, $mCB);
        }
    }

    protected $aEventCBMap = array();

    /**
     * @param string $sEventName
     * @param array  $aParam
     */
    public function occur($sEventName, $aParam = array())
    {
        if (isset($this->aEventCBMap[$sEventName])) {
            call_user_func_array($this->aEventCBMap[$sEventName], $aParam);
        }
    }

    /**
     * @param string $sEventName
     * @param mixed  $mCB
     */
    public function register($sEventName, $mCB)
    {
        $this->aEventCBMap[$sEventName] = $mCB;
    }

    /**
     * @param string $sEventName
     */
    public function unRegister($sEventName)
    {
        if (isset($this->aEventCBMap[$sEventName])) {
            unset($this->aEventCBMap[$sEventName]);
        }
    }

    /**
     * @param string $sEventName
     *
     * @return mixed
     */
    public function getEventCB($sEventName)
    {
        return isset($this->aEventCBMap[$sEventName]) ? $this->aEventCBMap[$sEventName] : null;
    }
}
