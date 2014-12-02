<?php
if (!isset($argv[1])) {
    exit("please print Author:AppName:NS for argv1\n");
}
if (!isset($argv[2])) {
    exit("please print TargetDir for argv2\n");
}

$sAppName = $argv[1];
$aArr     = explode(':', $argv[1]);
if (count($aArr) !== 3) {
    exit("argv1 must be format with Author:AppName:NS\n");
}
list($sAuthorName, $sAppName, $sNS) = $aArr;

$sTargetDir = $argv[2];

$sMode     = empty($argv[3]) ? 'AppSTD' : $argv[3];
$sTimeZone = empty($argv[4]) ? 'PRC' : $argv[4];

generate_app(dirname(__DIR__) . "/src/Bundle/Framework/__{$sMode}__", $sTargetDir, $sAuthorName, $sAppName, $sNS, $sTimeZone);

function generate_app($sSourceDir, $sTargetDir, $sAuthorName, $sAppName, $sNS, $sTimeZone, $i = 0)
{
    if (!is_dir($sTargetDir)) {
        mkdir($sTargetDir);
    }

    $rDir = opendir($sSourceDir);
    if ($rDir === false) {
        exit(str_repeat("\t", $i) . "Open dir $sSourceDir failed\n");
    }
    while (($sFile = readdir($rDir)) !== false) {
        if ($sFile[0] === '.') {
            continue;
        }
        $sSourceFile = "$sSourceDir/$sFile";
        if (is_file($sSourceFile)) {
            $sTargetFile = $sTargetDir . '/' . substr($sFile, 0, strlen($sFile) - 4);

            if (file_exists($sTargetFile)) {
                echo str_repeat("\t", $i) . "File $sTargetFile has been exists\n";
                continue;
            }
            if (copy($sSourceFile, $sTargetFile)) {
                echo str_repeat("\t", $i) . "Copy to $sTargetFile ok\n";
            } else {
                exit(str_repeat("\t", $i) . "Copy $sSourceFile to $sTargetFile failed\n");
            }

            $b = file_put_contents(
                $sTargetFile,
                str_replace(
                    array('{{{APP}}}', '{{{APP_NAME}}}', '{{{AUTHOR}}}', '{{{TIME_ZONE}}}'),
                    array($sNS, $sAppName, $sAuthorName, $sTimeZone),
                    file_get_contents($sTargetFile)
                )
            );
            if ($b === false) {
                exit(str_repeat("\t", $i) . "Generate file $sTargetFile failed\n");
            } else {
                echo str_repeat("\t", $i) . "Generate $sTargetFile ok\n";
            }
        } else {
            generate_app($sSourceFile, "$sTargetDir/$sFile", $sAuthorName, $sAppName, $sNS, $sTimeZone, $i + 1);
        }
    }
}

