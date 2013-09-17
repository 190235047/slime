<?php
namespace Slime;

/**
 * Class View
 * @package Slime\Framework
 * @author smallslime@gmail.com
 * @version 1.0
 */
class Viewer
{
    private $sTplDir;
    private $sTpl;
    private $aData = array();

    /**
     * @param string $sK
     * @param mixed $mV
     * @param bool $bReplace
     * @return $this
     */
    public function addData($sK, $mV, $bReplace = true)
    {
        if (!isset($this->aData[$sK]) || (isset($this->aData[$sK]) && $bReplace)) {
            $this->aData[$sK] = $mV;
        }
        return $this;
    }

    /**
     * @param array $mapKV
     * @param bool $replace
     * @return $this
     */
    public function addDatas(array $mapKV, $replace = true)
    {
        $replace ? $this->aData = array_merge($this->aData, $mapKV) : array($mapKV, $this->aData);
        return $this;
    }

    /**
     * @param string $tplDir
     */
    public function setTplDir($tplDir)
    {
        $this->sTplDir = $tplDir[strlen(
            $tplDir
        ) - 1] == DIRECTORY_SEPARATOR ? $tplDir : ($tplDir . DIRECTORY_SEPARATOR);
    }

    /**
     * @param string $sTpl
     * @return $this
     */
    public function setTpl($sTpl)
    {
        $this->sTpl = str_replace('.', '/', $sTpl) . '.php';
        return $this;
    }

    public function subRender($sTpl, $aData = array())
    {
        $View = clone $this;
        $View->setTpl($sTpl);
        if (!empty($aData)) {
            $View->addDatas($aData);
        }
        return $View->render();
    }

    /**
     * @param bool $bEcho
     * @return mixed string|null
     */
    public function render($bEcho = true)
    {
        extract($this->aData);
        ob_start();
        require $this->sTplDir . $this->sTpl;
        $sResult = ob_get_contents();
        ob_end_clean();

        if ($bEcho) {
            echo $sResult;
            return null;
        } else {
            return $sResult;
        }
    }
}

