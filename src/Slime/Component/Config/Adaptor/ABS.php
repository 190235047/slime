<?php
namespace Slime\Component\Config;

/**
 * Class Adaptor_ABS
 *
 * @package Slime\Component\Config
 * @author  smallslime@gmail.com
 */
abstract class Adaptor_ABS implements IAdaptor
{
    protected $bParseMode = true;
    protected $bToBeResetParseMode = false;
    /**
     * @param bool $bParse
     *
     * @return $this
     */
    public function setParseMode($bParse = true)
    {
        $this->bParseMode = $bParse;
        return $this;
    }

    /**
     * @param bool $bParse
     *
     * @return $this
     */
    public function setTmpParseMode($bParse = false)
    {
        $this->bToBeResetParseMode = $this->bParseMode;
        $this->bParseMode = $bParse;
        return $this;
    }

    /**
     * @return $this
     */
    public function resetParseMode()
    {
        if ($this->bToBeResetParseMode!==null) {
            $this->bParseMode = $this->bToBeResetParseMode;
            $this->bToBeResetParseMode = null;
        }
        return $this;
    }
}