<?php
namespace {{{NS}}}\ControllerCli;

use {{{NS}}}\System\Controller\Page_Base;

class C_Main extends Page_Base
{
    public function actionDefault()
    {
        $this->aData['title'] = 'MyFirst App';
        $this->aData['h1']    = 'Hello world';
    }
}