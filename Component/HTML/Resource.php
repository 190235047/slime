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

    public function __construct($sBaseUrl = null)
    {
        $this->sBaseUrl = $sBaseUrl;
        if (empty($this->sBaseUrl)) {
            $this->sBaseUrl = sprintf('http://%s', $_SERVER['HTTP_HOST']);
        }
    }

    public function gentCSS($sName, $sPath = 'css')
    {
        return sprintf('<link href="%s" rel="stylesheet">', $this->sBaseUrl . '/' . $sPath . '/' . $sName);
    }

    public function gentCSSs($aMapPath2Name)
    {
        $sResult = '';
        foreach ($aMapPath2Name as $mK => $sV) {
            $sResult .= $this->gentCSS($sV, is_string($mK) ? $mK : 'css');
        }
        return $sResult;
    }

    public function gentJS($sName, $sPath = 'js')
    {
        return sprintf('<script type="text/javascript" src="%s"></script>', $this->sBaseUrl . '/' . $sPath . '/' . $sName);
    }

    public function gentJSs($aMapPath2Name)
    {
        $sResult = '';
        foreach ($aMapPath2Name as $mK => $sV) {
            $sResult .= $this->gentJS($sV, is_string($mK) ? $mK : 'js');
        }
        return $sResult;
    }
}
