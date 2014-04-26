<?php
namespace {{{NS}}}\ControllerApi;

use {{{NS}}}\System\Controller\Api_Base;

class V1_Main extends Api_Base
{
    public function actionDefault()
    {
        $this->aData['foo'] = 'bar';
    }
}