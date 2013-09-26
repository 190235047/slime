<?php
namespace SlimeFramework\Component\View;

use Psr\Log\LoggerInterface;

/**
 * Class Adaptor_PHP
 *
 * @package SlimeFramework\Component\View
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
        return $this;
    }

    /**
     * @param string $sTpl
     *
     * @return $this
     */
    public function setTpl($sTpl)
    {
        $this->sTpl = $this->sBaseDir . DIRECTORY_SEPARATOR . $sTpl;
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
     * @return string
     */
    public function renderAsResult()
    {
        if (!file_exists($this->sTpl)) {
            $this->Log->error('Template file[{file}] is not exist', array('file' => $this->sTpl));
            exit(1);
        }
        extract($this->aData);
        ob_start();
        require $this->sTpl;
        $sResult = ob_get_contents();
        ob_end_clean();
        return $sResult;
    }

    public function subRender($sTpl, array $aData = array())
    {
        $View = clone $this;
        $View->setTpl($sTpl);
        if (!empty($aData)) {
            $View->assignMulti($aData);
        }
        return $View->renderAsResult();
    }
}