<?php
if (!isset($argv[1])) {
    exit('please print appName:appNS');
}
if (!isset($argv[2])) {
    exit('please print appPath');
}

$sAppName = $argv[1];
if (strpos($sAppName, '.')!==false) {
    list($sAppName, $sAppNSName) = explode('.', $sAppName);
} else {
    $sAppNSName = $sAppName;
}

$sPath = $argv[2];
if (!file_exists($sPath)) {
    exec("mkdir -p $sPath");
}
if (!file_exists($sPath)) {
    exit("Create path $sPath failed. please do it manual");
}

$sMode = empty($argv[3]) ? 'standard' : $argv[3];

switch ($sMode) {
    case 'mini':
        break;
    case 'standard':
        break;
    case 'restful':
        break;
    default:
        exit("No support mode $sMode");
}