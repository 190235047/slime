<?php
namespace {{{NS}}}\ControllerPage;

use {{{NS}}}\System\Controller\Page_Base;

class C_Main extends Page_Base
{
    public function actionDefault()
    {
        $this->aData['title'] = 'MyFirst App';
        $this->aData['h1']    = __('common.hi');
    }
}