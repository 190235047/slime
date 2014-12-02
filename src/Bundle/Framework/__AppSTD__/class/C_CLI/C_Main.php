<?php
namespace AppSTD\C_CLI;

use Slime\Bundle\Framework\Controller_Cli;
use Slime\Component\Http\Ext;

class C_Main extends Controller_Cli
{
    public function actionDefault()
    {
        Ext::ev_LogCost($this->CTX->Event, $this->CTX->Log);

        $HC  = $this->CTX->HttpCall;
        $mRS = $HC->setUrl('http://www.baidu.com')->get()->asString();
        var_dump($mRS);
    }
}