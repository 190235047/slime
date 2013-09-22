<?php
namespace SlimeFramework\Component\HTML;

/**
 * Class Resource
 * @package SlimeFramework\Component\HTML
 * @author smallslime@gmail.com
 * @version 0.1
 */
class Resource
{
    private $sBaseUrl;

    public function __construct($sBaseUrl)
    {
        $this->sBaseUrl = rtrim($sBaseUrl, '/');
    }

    public function gentCSS($sName, $sPath = 'css')
    {
        return sprintf('<link href="%s" rel="stylesheet">', $this->sBaseUrl . '/' . $sPath . '/' . $sName);
    }

    public function gentCSSs($aName, $sPath = 'css')
    {
        $sResult = '';
        foreach ($aName as $sV) {
            $sResult .= $this->gentCSS($sV, $sPath);
        }
        return $sResult;
    }

    public function gentJS($sName, $sPath = 'js')
    {
        return sprintf('<script type="text/javascript" src="%s"></script>', $this->sBaseUrl . '/' . $sPath . '/' . $sName);
    }

    public function gentJSs($aName, $sPath = 'js')
    {
        $sResult = '';
        foreach ($aName as $sV) {
            $sResult .= $this->gentJS($sV, $sPath);
        }
        return $sResult;
    }
}
