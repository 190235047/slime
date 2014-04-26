<?php
namespace Slime\Component\Log;

use Slime\Component\Helper\Sugar;

/**
 * Class Logger
 *
 * @package Slime\Component\Log
 * @author  smallslime@gmail.com
 */
class Logger implements LoggerInterface
{
    const DESC_EMERGENCY = 'emergency';
    const DESC_ALERT     = 'alert';
    const DESC_CRITICAL  = 'critical';
    const DESC_ERROR     = 'error';
    const DESC_WARNING   = 'warning';
    const DESC_NOTICE    = 'notice';
    const DESC_INFO      = 'info';
    const DESC_DEBUG     = 'debug';

    const LEVEL_ALL       = 255;
    const LEVEL_EMERGENCY = 128;
    const LEVEL_ALERT     = 64;
    const LEVEL_CRITICAL  = 32;
    const LEVEL_ERROR     = 16;
    const LEVEL_WARNING   = 8;
    const LEVEL_NOTICE    = 4;
    const LEVEL_INFO      = 2;
    const LEVEL_DEBUG     = 1;

    public static $aMap = array(
        self::LEVEL_EMERGENCY => 'emergency',
        self::LEVEL_ALERT     => 'alert',
        self::LEVEL_CRITICAL  => 'critical',
        self::LEVEL_ERROR     => 'error',
        self::LEVEL_WARNING   => 'warning',
        self::LEVEL_NOTICE    => 'notice',
        self::LEVEL_INFO      => 'info',
        self::LEVEL_DEBUG     => 'debug'
    );

    /**
     * @param array $aWriterConf ['@File' => ['param1', 'param2'], '@FirePHP']
     * @param int   $iLogLevel
     * @param null  $sRequestID
     */
    public function __construct(
        array $aWriterConf,
        $iLogLevel = self::LEVEL_ALL,
        $sRequestID = null
    ) {
        $this->aWriter   = self::createWriter($aWriterConf);
        $this->iLogLevel = $iLogLevel;
        $this->sGUID     = $sRequestID ? $sRequestID : md5(uniqid(__CLASS__, true));
    }

    /**
     * @param $aWriterConf ['@File' => ['param1', 'param2'], '@FirePHP']
     *
     * @return IWriter[]
     */
    public static function createWriter($aWriterConf)
    {
        $aWriter = array();
        foreach ($aWriterConf as $sK => $mParam) {
            $aClassAndArgs = array();
            if (is_int($sK)) {
                $aClassAndArgs[] = (string)$mParam;
            } else {
                $aClassAndArgs[] = $sK;
                if (!empty($mParam) && is_array($mParam)) {
                    $aClassAndArgs = array_merge($aClassAndArgs, $mParam);
                }
            }
            $aWriter[] = Sugar::createObjAdaptor(__NAMESPACE__, $aClassAndArgs, 'IWriter', 'Writer_');
        }
        return $aWriter;
    }

    /**
     * System is unusable.
     *
     * @param string $sMessage
     * @param array  $aContext
     *
     * @return null
     */
    public function emergency($sMessage, array $aContext = array())
    {
        $this->log(self::LEVEL_EMERGENCY, $sMessage, $aContext);
    }

    /**
     * Action must be taken immediately.
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $sMessage
     * @param array  $aContext
     *
     * @return null
     */
    public function alert($sMessage, array $aContext = array())
    {
        $this->log(self::LEVEL_ALERT, $sMessage, $aContext);
    }

    /**
     * Critical conditions.
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $sMessage
     * @param array  $aContext
     *
     * @return null
     */
    public function critical($sMessage, array $aContext = array())
    {
        $this->log(self::LEVEL_CRITICAL, $sMessage, $aContext);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $sMessage
     * @param array  $aContext
     *
     * @return null
     */
    public function error($sMessage, array $aContext = array())
    {
        $this->log(self::LEVEL_ERROR, $sMessage, $aContext);
    }

    /**
     * Exceptional occurrences that are not errors.
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $sMessage
     * @param array  $aContext
     *
     * @return null
     */
    public function warning($sMessage, array $aContext = array())
    {
        $this->log(self::LEVEL_WARNING, $sMessage, $aContext);
    }

    /**
     * Normal but significant events.
     *
     * @param string $sMessage
     * @param array  $aContext
     *
     * @return null
     */
    public function notice($sMessage, array $aContext = array())
    {
        $this->log(self::LEVEL_NOTICE, $sMessage, $aContext);
    }

    /**
     * Interesting events.
     * Example: User logs in, SQL logs.
     *
     * @param string $sMessage
     * @param array  $aContext
     *
     * @return null
     */
    public function info($sMessage, array $aContext = array())
    {
        $this->log(self::LEVEL_INFO, $sMessage, $aContext);
    }

    /**
     * Detailed debug information.
     *
     * @param string $sMessage
     * @param array  $aContext
     *
     * @return null
     */
    public function debug($sMessage, array $aContext = array())
    {
        $this->log(self::LEVEL_DEBUG, $sMessage, $aContext);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param int    $iLevel
     * @param string $sMessage
     * @param array  $aContext
     *
     * @return void
     */
    public function log($iLevel, $sMessage, array $aContext = array())
    {
        if (!($iLevel & $this->iLogLevel)) {
            return;
        }

        $sMessage = self::interpolate($sMessage, $aContext);
        list($sUSec, $sSec) = explode(' ', microtime());
        $sTime = date('Y-m-d H:i:s', $sSec) . '.' . substr($sUSec, 2, 4);

        $aRow = array('sTime' => $sTime, 'iLevel' => $iLevel, 'sMessage' => $sMessage, 'sGuid' => $this->sGUID);
        foreach ($this->aWriter as $Writer) {
            $Writer->acceptData($aRow);
        }
    }

    /**
     * @param int $iLevel
     *
     * @return bool
     */
    public function needLog($iLevel)
    {
        return (bool)($iLevel & $this->iLogLevel);
    }

    public static function getLevelString($iLevel)
    {
        return isset(self::$aMap[$iLevel]) ? self::$aMap[$iLevel] : 'unknown';
    }

    /**
     * Interpolates context values into the message placeholders.
     *
     * @param string $sMessage
     * @param array  $aContext
     *
     * @return string
     */
    public static function interpolate($sMessage, array $aContext = array())
    {
        // build a replacement array with braces around the context keys
        $aReplace = array();
        foreach ($aContext as $sK => $mV) {
            $aReplace['{' . $sK . '}'] = (is_array($mV) || is_object($mV)) ? json_encode($mV) : (string)$mV;
        }

        // interpolate replacement values into the message and return
        return strtr($sMessage, $aReplace);
    }
}