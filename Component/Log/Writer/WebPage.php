<?php
namespace SlimeFramework\Component\Log;

class Writer_WebPage implements IWriter
{
    public $sDebugLayer;

    protected $aData = array();

    public function __construct($sDebugLayer = null)
    {
        if ($sDebugLayer!==null) {
            $this->sDebugLayer = $sDebugLayer;
        }
    }

    public function acceptData($aRow)
    {
        $this->aData[] = $aRow;
    }

    public function __destruct()
    {
        if ($this->sDebugLayer===null) {
            $sLi       = $sUl = '';
            $aTidyData = array();
            foreach ($this->aData as $aRow) {
                $aTidyData[$aRow['iLevel']][] = $aRow;
            }
            foreach ($aTidyData as $iK => $aRow) {
                if (empty($aRow)) {
                    continue;
                }
                $sLi .= sprintf('<li onclick="_sf_debug_show(this)">%s</li>', Logger::getLevelString($iK));
                $aHtml = array();
                foreach ($aRow as $aItem) {
                    $sHtml = "<div><span class='tip'>Message</span><span>{$aItem['sMessage']}</span></div>";
                    $sHtml .= "<div><span class='tip'>Time</span><span>{$aItem['sTime']}</span></div>";
                    $aHtml[] = $sHtml;
                }
                $sUl .= '<ul><li>' . implode('</li><li>', $aHtml) . '</li></ul>';
            }

            $sResult = <<<HTML
    <style>
    #_sf_debug {position: absolute; top:20px; right:0; background-color: #696969; width: 700px; color: #dcdcdc;display: none;}
    #_sf_debug ul,#_sf_debug li {margin:0;padding:0}
    #_sf_debug li {list-style-type:none}
    #_sf_debug_nav li {display: inline-block; padding:5px 10px; background-color: #00008b; cursor: pointer;}
    #_sf_debug_content {padding-bottom: 10px;}
    #_sf_debug_content ul {display: none;}
    #_sf_debug_content li {margin: 10px 0 0 10px;}
    #_sf_debug_content li span {display: inline-block;}
    #_sf_debug_content li span.tip {width: 100px;}
    #_sf_debug_content li span.tip2 {width: 200px;}
    </style>
    <div style="position: absolute; top:0; right:0; background-color: red; width: 20px; height: 20px; cursor: pointer;" onclick="_sf_debug_toggle();"></div>
    <div id="_sf_debug">
        <ul id="_sf_debug_nav">{$sLi}</ul>
        <div id="_sf_debug_content">{$sUl}</div>
    </div>

    <script>
    var _sf_debug_e = document.getElementById('_sf_debug');
    var _sf_debug_eLi = document.getElementById('_sf_debug_nav').getElementsByTagName('li');
    var _sf_debug_eCont = document.getElementById('_sf_debug_content').getElementsByTagName('ul');
    var _sf_debug_num = _sf_debug_eLi.length;
    function _sf_debug_toggle() {
        if (_sf_debug_e.style.display=='none' || _sf_debug_e.style.display=='') {
            _sf_debug_e.style.display = 'block';
        } else {
            _sf_debug_e.style.display = 'none';
        }
    }
    function _sf_debug_show(e) {
        for (var i=0; i<_sf_debug_num; i++) {
            if (_sf_debug_eLi[i]!=e) {
                _sf_debug_resetNavLi(_sf_debug_eLi[i]);
                _sf_debug_eCont[i].style.display = 'none';
            } else {
                _sf_debug_hoverNavLi(_sf_debug_eLi[i]);
                _sf_debug_eCont[i].style.display = 'block';
            }
        }
    }
    function _sf_debug_hoverNavLi(e) {
        e.style.backgroundColor = "#5bc0de";
        e.style.color = "black";
    }
    function _sf_debug_resetNavLi(e) {
        e.style.backgroundColor = "#00008b";
        e.style.color = "#777777";
    }
    if(_sf_debug_eLi.length>0){
        _sf_debug_eLi[0].click();
    }
    </script>
HTML;
        } else {
            $aData = $this->aData;
            $sResult = require $this->sDebugLayer;
        }

        echo $sResult;
    }
}