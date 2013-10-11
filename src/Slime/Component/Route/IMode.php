<?php
namespace Slime\Component\Route;

use Slime\Component\Http;

interface IMode
{
    /**
     * @param \Slime\Component\Http\HttpRequest   $Request
     * @param \Slime\Component\Route\CallBack     $CallBack
     *
     * @return bool [true:continue next rule, false||other:break{default action}]
     */
    public function runHttp(Http\HttpRequest $Request, CallBack $CallBack);

    public function runCli($aArg, CallBack $CallBack);
}