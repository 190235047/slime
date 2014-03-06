<?php
namespace Slime\Component\Log;

use DebugBar;
use Slime\Bundle\Framework\Context;

/**
 * Class Writer_DebugBar
 *
 * @package Slime\Component\Log
 * @author  smallslime@gmail.com
 */
class Writer_DebugBar implements IWriter
{
    public $sFormat = ":sMessage";

    protected $bCouldLog = null;
    protected $DebugBar;
    protected $aCachedData = array();

    public function __construct()
    {
        $this->DebugBar = new DebugBar\DebugBar();
        $this->DebugBar
            ->addCollector(new DebugBar\DataCollector\RequestDataCollector())
            ->addCollector(new DebugBar_AllTimeOnly());
    }

    public function acceptData($aRow)
    {
        if ($this->bCouldLog === null) {
            $sStr = str_replace(
                array(':sTime', ':iLevel', ':sMessage', ':sGuid'),
                array($aRow['sTime'], Logger::getLevelString($aRow['iLevel']), $aRow['sMessage'], $aRow['sGuid']),
                $this->sFormat
            );

            if (!Context::getInst()->isRegister('HttpRequest')) {
                $this->aCachedData[] = array(0 => $sStr, 1 => $aRow['iLevel'], 2 => $aRow['sTime']);
                return;
            }
            $this->bCouldLog = !Context::getInst()->HttpRequest->isAjax();
            if ($this->bCouldLog && !empty($this->aCachedData)) {
                foreach ($this->aCachedData as $aItem) {
                    $this->_addMessage($aItem[0], Logger::getLevelString($aItem[1]), $aItem[2]);
                }
            }
        }

        if ($this->bCouldLog === false) {
            return;
        } else {
            $sStr = str_replace(
                array(':sTime', ':iLevel', ':sMessage', ':sGuid'),
                array($aRow['sTime'], Logger::getLevelString($aRow['iLevel']), $aRow['sMessage'], $aRow['sGuid']),
                $this->sFormat
            );

            $this->_addMessage($sStr, Logger::getLevelString($aRow['iLevel']), $aRow['sTime']);
        }
    }

    private function _addMessage($sStr, $sLevel, $sTime)
    {
        $aArr = explode(' : ', $sStr, 2);
        if (count($aArr) !== 2) {
            $aArr = array(0 => 'Messages', $sStr);
        }
        try {
            $this->DebugBar->getCollector($aArr[0]);
            $this->DebugBar[$aArr[0]]->addMessage($sTime . ' : ' . $aArr[1], $sLevel);
        } catch (DebugBar\DebugBarException $E) {
            $this->DebugBar->addCollector(new DebugBar\DataCollector\MessagesCollector($aArr[0]));
            $this->DebugBar[$aArr[0]]->addMessage($sTime . ' : ' . $aArr[1], $sLevel);
        }
    }

    public function __destruct()
    {
        if ($this->bCouldLog) {
            $DebugBarRender = $this->DebugBar->getJavascriptRenderer();
            $DebugBarRender->setOptions(array('base_url' => '/debugbar/'));
            echo $DebugBarRender->renderHead();
            echo $DebugBarRender->render();
        }
    }
}

class DebugBar_AllTimeOnly extends DebugBar\DataCollector\TimeDataCollector
{
    public function getWidgets()
    {
        return array(
            "time" => array(
                "icon"    => "time",
                "tooltip" => "Request Duration",
                "map"     => "time.duration_str",
                "default" => "'0ms'"
            )
        );
    }
}