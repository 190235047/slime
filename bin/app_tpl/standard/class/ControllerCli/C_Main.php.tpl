<?php
namespace {{{NS}}}\ControllerCli;

use {{{NS}}}\System\Controller\Cli_Base;

class C_Main extends Cli_Base
{
    public function actionDefault()
    {
        echo __('common.hi');
    }
}