<?php
namespace SlimeFramework\Component\Route;

use SlimeFramework\Component\Http;

interface IMode
{
    /**
     * @param \SlimeFramework\Component\Http\IRequest $Request
     * @param \SlimeFramework\Component\Route\CallBack $CallBack
     * @return bool [true:continue next rule, false||other:break{default action}]
     */
    public function run(Http\IRequest $Request, CallBack $CallBack);
}