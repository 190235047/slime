<?php
namespace Slime\Component\View;

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

    /**
     * @param string|null $sBaseDir
     */
    public function __construct($sBaseDir = null)
    {
        if ($sBaseDir!==null) {
            $this->sBaseDir = $sBaseDir;
        }
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
     * @throws \Exception
     * @return string
     */
    public function renderAsResult()
    {
        $sFile = $this->sBaseDir . DIRECTORY_SEPARATOR . $this->sTpl;
        if (!file_exists($sFile)) {
            throw new \Exception("Template file[{$this->sTpl}] is not exist");
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
        if (!empty($aData)) {
            $View->assignMulti($aData);
        }
        return $View->renderAsResult(true);
    }

    /**
     * @return string
     */
    public function getBaseDir()
    {
        return $this->sBaseDir;
    }

    /**
     * @return string
     */
    public function getTpl()
    {
        return $this->sTpl;
    }
}
