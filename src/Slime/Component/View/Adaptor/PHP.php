<?php
namespace Slime\Component\View;

use Psr\Log\LoggerInterface;

/**
 * Class Adaptor_PHP
 *
 * @package Slime\Component\View
 * @author  smallslime@gmail.com
 */
class Adaptor_PHP implements IAdaptor
{
    private $sBaseDir;
    private $sTpl;

    private $aData = array();

    public function __construct(LoggerInterface $Log)
    {
        $this->Log = $Log;
    }

    /**
     * @param string $sBaseDir
     *
     * @return $this
     */
    public function setBaseDir($sBaseDir)
    {
        $this->sBaseDir = $sBaseDir;
        $this->Log->debug('Tpl base dir[{dir}]', array('dir' => $sBaseDir));
        return $this;
    }

    /**
     * @param string $sTpl
     *
     * @return $this
     */
    public function setTpl($sTpl)
    {
        $this->sTpl = $sTpl;
        return $this;
    }

    /**
     * @param string $sK
     * @param mixed  $mV
     * @param bool   $bOverwrite
     *
     * @return $this
     */
    public function assign($sK, $mV, $bOverwrite = true)
    {
        if ($bOverwrite) {
            $this->aData[$sK] = $mV;
        } elseif (!isset($this->aData[$sK])) {
            $this->aData[$sK] = $mV;
        }

        return $this;
    }

    /**
     * @param array $aKVMap
     * @param bool  $bOverwrite
     *
     * @return $this
     */
    public function assignMulti($aKVMap, $bOverwrite = true)
    {
        if ($bOverwrite) {
            $this->aData = array_merge($this->aData, $aKVMap);
        } else {
            $this->aData = array_merge($aKVMap, $this->aData);
        }

        return $this;
    }

    /**
     * @return void
     */
    public function render()
    {
        echo $this->renderAsResult();
    }

    /**
     * @param bool $bIsSub
     * @return string
     */
    public function renderAsResult($bIsSub = false)
    {
        $sFile = $this->sBaseDir . DIRECTORY_SEPARATOR . $this->sTpl;
        if (!file_exists($sFile)) {
            $this->Log->error('Template file[{file}] is not exist', array('file' => $this->sTpl));
            exit(1);
        }
        if (!$bIsSub) {
            $this->Log->debug('TPL[{tpl}]', array('tpl' => $this->sTpl));
        }
        extract($this->aData);
        ob_start();
        require $sFile;
        $sResult = ob_get_contents();
        ob_end_clean();
        return $sResult;
    }

    public function subRender($sTpl, array $aData = array())
    {
        $View = clone $this;
        $View->setTpl($sTpl);
        $this->Log->debug('Sub TPL[{tpl}]', array('tpl' => $sTpl));
        if (!empty($aData)) {
            $View->assignMulti($aData);
        }
        return $View->renderAsResult(true);
    }
}