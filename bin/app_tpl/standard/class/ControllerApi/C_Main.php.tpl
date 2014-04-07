<?php
namespace {{{NS}}}\ControllerApi;

use {{{NS}}}\System\Controller\Api_Base;

class C_Main extends Api_Base
{
    public function actionDefault()
    {
        $this->aData['foo'] = 'bar';
    }
}