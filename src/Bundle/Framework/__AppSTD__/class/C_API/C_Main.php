<?php
namespace AppSTD\C_API;

use AppSTD\System\Framework\C_API;

class C_Main extends C_API
{
    public function get()
    {
        $this->success(array('foo' => 'bar'));
    }
}