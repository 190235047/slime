<?php
namespace Slime\Component\Route;

class HitMode
{
    const M_MAIN_STOP     = 1;
    const M_NOT_MAIN_STOP = 2;
    const M_MAIN_GOON     = 3;
    const M_NOT_MAIN_GOON = 4;

    protected $iMode = self::M_MAIN_STOP;

    public function setAsCommon()
    {
        $this->iMode = self::M_MAIN_STOP;
    }

    /**
     * @param int $iMode
     *
     * @throws \InvalidArgumentException
     */
    public function setMode($iMode)
    {
        // write this way for easy
        if (!is_int($iMode) || $iMode < 1 || $iMode > 4) {
            throw new \InvalidArgumentException("param must be HitMode::MODE_STOP_AS_*");
        }
        $this->iMode = $iMode;
    }

    public function ifNeedGoOn()
    {
        return $this->iMode===self::M_MAIN_GOON || $this->iMode===self::M_NOT_MAIN_GOON;
    }

    public function isMainLogic()
    {
        return $this->iMode===self::M_MAIN_GOON || $this->iMode===self::M_MAIN_STOP;
    }
}