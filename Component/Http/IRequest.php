<?php
namespace SlimeFramework\Component\Http;

interface IRequest
{
    public function getRequestMethod();

    public function getRequestURI();

    public function getProtocol();

    public function getHeader($sHeader);

    public function setXSSFilter($bXssFilter = true);

    public function getCookie($sKey, $bXssFilter = null);

    public function getGet($sKey, $bXssFilter = null);

    public function getPost($sKey, $bXssFilter = null);

    public function getGetPost($sKey, $bGetFirst = true, $bXssFilter = null);

    public function getContents();

    /**
     * @return bool
     */
    public function isAjax();
}