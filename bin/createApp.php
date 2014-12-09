<?php
if (!isset($argv[1])) {
    exit("Args Format: php createApp.php Author:AppName:NS TargetDir\n");
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
$sMode      = empty($argv[3]) ? 'AppSTD' : $argv[3];

generate_app($sMode, dirname(__DIR__) . "/src/Bundle/Framework/__{$sMode}__",
    $sTargetDir, $sAuthorName, $sAppName, $sNS);

function generate_app($sMode, $sSourceDir, $sTargetDir, $sAuthorName, $sAppName, $sNS, $i = 0)
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
            $sTargetFile = $sTargetDir . '/' . $sFile;

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
                    array($sMode, '{{{APP_NAME}}}', '{{{AUTHOR}}}'),
                    array($sNS, $sAppName, $sAuthorName),
                    file_get_contents($sTargetFile)
                )
            );
            if ($b === false) {
                exit(str_repeat("\t", $i) . "Generate file $sTargetFile failed\n");
            } else {
                echo str_repeat("\t", $i) . "Generate $sTargetFile ok\n";
            }
        } else {
            generate_app($sMode, $sSourceFile, "$sTargetDir/$sFile", $sAuthorName, $sAppName, $sNS, $i + 1);
        }
    }
}

