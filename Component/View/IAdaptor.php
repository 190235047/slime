<?php
namespace SlimeFramework\Component\View;

interface IAdaptor
{
    /**
     * @param string $sBaseDir
     * @return $this
     */
    public function setBaseDir($sBaseDir);

    /**
     * @param string $sTpl
     * @return $this
     */
    public function setTpl($sTpl);

    /**
     * @param string $sK
     * @param mixed $mV
     * @param bool $bOverwrite
     * @return $this
     */
    public function assign($sK, $mV, $bOverwrite = true);

    /**
     * @param array $aKVMap
     * @param bool $bOverwrite
     * @return $this
     */
    public function assignMulti($aKVMap, $bOverwrite = true);

    /**
     * @return void
     */
    public function render();

    /**
     * @return string
     */
    public function renderAsResult();

    /**
     * @param string $sTpl
     * @param array $aData
     * @return string
     */
    public function subRender($sTpl, array $aData = array());
}