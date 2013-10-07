<?php
namespace SlimeFramework\Component\Html;

class Pagination_Bean
{
    public $iPre;
    public $aMiddle;
    public $iNext;
    public $iPageTotal;

    public function __construct($iPre, array $aMiddle, $iNext, $iPageTotal)
    {
        $this->iPre       = $iPre;
        $this->aMiddle    = $aMiddle;
        $this->iNext      = $iNext;
        $this->iPageTotal = $iPageTotal;
    }

    public function renderStandard()
    {
        ;
    }

    public function renderWithCB()
    {
        ;
    }
}