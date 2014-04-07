<?php
namespace {{{NS}}};

date_default_timezone_set('{{{TIME_ZONE}}}');

if (!defined('DIR_PUBLIC')) {
    define('DIR_PUBLIC', __DIR__);
    define('DIR_BASE', dirname(DIR_PUBLIC));
    define('DIR_CONFIG', DIR_BASE . '/config');
    define('DIR_LANGUAGE', DIR_BASE . '/language');
    define('DIR_VIEW', DIR_BASE . '/view');
    define('DIR_CLASS', DIR_BASE . '/class');
}

require __DIR__ . '/../vendor/autoload.php';

if (file_exists(DIR_CLASS . '/Helper/__Alias__.php')) {
    require_once DIR_CLASS . '/Helper/__Alias__.php';
}
