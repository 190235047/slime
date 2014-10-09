<?php
namespace Slime\Component\RDBMS\DAL;

/**
 * Class Val
 *
 * @package Slime\Component\RDBMS\DAL
 * @author  smallslime@gmail.com
 */
class Val
{
    public static function Val($mV)
    {
        return new self($mV, 0);
    }

    public static function ValPre($mV, $sPre = ':')
    {
        return new self($mV, 1, $sPre);
    }

    public static function Name($mV)
    {
        return new self($mV, 2);
    }

    public static function Keyword($mV)
    {
        return new self($mV, 3);
    }

    public static function K_OP_V($mK, $mV, $sOP = '=')
    {
        return new self(array($mK, $mV, $sOP), 4);
    }

    protected $iType;
    protected $mV;
    protected $nsPre;

    private function __construct($mV, $iType, $nsPre = null)
    {
        $this->mV    = $mV;
        $this->iType = $iType;
        if ($nsPre !== null) {
            $this->nsPre = $nsPre;
        }
    }

    public function __toString()
    {
        switch ($this->iType) {
            case 1:
                return $this->nsPre . (string)$this->mV;
            case 2:
                return '`' . (string)$this->mV . '`';
            case 3:
                return (string)$this->mV;
            case 4:
                $sK = is_string($this->mV[0]) ? "`{$this->mV[0]}`" : (string)$this->mV[0];
                $sV = is_string($this->mV[1]) ? "'{$this->mV[1]}'" : (string)$this->mV[1];
                return "$sK {$this->mV[2]} $sV";
            default:
                return is_string($this->mV) ? "'{$this->mV}'" : (string)$this->mV;
        }
    }
}