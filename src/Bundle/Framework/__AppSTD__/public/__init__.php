<?php
date_default_timezone_set('PRC');

define('DIR_BASE', dirname(__DIR__));
define('DIR_CLASS', DIR_BASE . '/class');
define('DIR_CONFIG', DIR_BASE . '/config');
define('DIR_I18N', DIR_BASE . '/i18n');
define('DIR_PRIVATE', DIR_BASE . '/private');
define('DIR_PUBLIC', DIR_BASE . '/public');
define('DIR_VIEW', DIR_BASE . '/view');

#require DIR_BASE . '/vendor/autoload.php';

#debug
/** @var Composer\Autoload\ClassLoader $AL */
$AL = require DIR_BASE . '/../../../../vendor/autoload.php';
$AL->addPsr4('AppSTD\\', DIR_CLASS);

function dd($mV, $i=0)
{
    echo $i===0 ? '<pre>' : '';
    if (is_array($mV)) {
        $iC = count($mV);
        echo ("Array[$iC]->\n");
        foreach ($mV as $mK => $mVV) {
            __dd("\t$mK:", $i);
            dd($mVV, $i+1);
            echo "\n";
        }
    } else {
        if (is_object($mV)) {
            $Ref = new \ReflectionObject($mV);
            echo '(OBJ) ' . get_class($mV);
            foreach ($Ref->getProperties() as $mVVV) {
                var_dump($mVVV);
            }
        } else {
            echo '(' . gettype($mV) . ") $mV";
        }
    }
    echo $i===0 ? '</pre>' : '';
}

function __dd($sStr, $i)
{
    echo str_repeat("\t", $i) . $sStr;
}