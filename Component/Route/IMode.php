<?php
namespace SlimeFramework\Component\Route;

use SlimeFramework\Component\Http;

interface IMode
{
    /**
     * @param \SlimeFramework\Component\Http\Request $Request
     * @param \SlimeFramework\Component\Route\CallBack $CallBack
     * @return bool [true:continue next rule, false||other:break{default action}]
     */
    public function run(Http\Request $Request, CallBack $CallBack);
}