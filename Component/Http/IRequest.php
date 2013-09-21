<?php
namespace SlimeFramework\Component\Http;

interface IRequest
{
    public function getRequestMethod();

    public function getRequestURI();

    public function getProtocol();

    public function getHeader($sHeader);

    public function getCookie($sKey);

    public function getContents();

    /**
     * @return bool
     */
    public function isAjax();
}