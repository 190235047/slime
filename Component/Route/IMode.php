<?php
namespace SlimeFramework\Component\Route;

use SlimeFramework\Component\Http;

interface IMode
{
    /**
     * @param \SlimeFramework\Component\Http\HttpRequest   $Request
     * @param \SlimeFramework\Component\Route\CallBack     $CallBack
     *
     * @return bool [true:continue next rule, false||other:break{default action}]
     */
    public function runHttp(Http\HttpRequest $Request, CallBack $CallBack);

    public function runCli($aArg, CallBack $CallBack);
}