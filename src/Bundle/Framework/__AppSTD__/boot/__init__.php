<?php
//cd xxxx && php -S 0.0.0.0:80 xx.php 2>&1 > /dev/null &
$sRequestFile = __DIR__ . $_SERVER['REQUEST_URI'];
if (is_file($sRequestFile) && file_exists($sRequestFile)) {
    $aBlock = parse_url($_SERVER['REQUEST_URI']);
    $rPos   = strrpos($aBlock['path'], '.');
    $sExt   = $rPos === false ? '' : substr($aBlock['path'], $rPos + 1);
    $aMap   = array(
        'js'  => 'text/javascript',
        'css' => 'text/css'
    );
    if (isset($aMap[$sExt])) {
        header('Content-Type: ' . $aMap[$sExt]);
    }
    echo file_get_contents($sRequestFile);
    exit;
}
