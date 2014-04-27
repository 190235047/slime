<?php
namespace {{{NS}}};

use Slime\Bundle\Framework\Bootstrap;
use Slime\Component\Config\Configure;

date_default_timezone_set('{{{TIME_ZONE}}}');
define('DIR_PUBLIC', __DIR__);
define('DIR_BASE', dirname(DIR_PUBLIC));
define('DIR_CONFIG', DIR_BASE . '/config');
define('DIR_LANGUAGE', DIR_BASE . '/language');
define('DIR_VIEW', DIR_BASE . '/view');
define('DIR_CLASS', DIR_BASE . '/class');

require __DIR__ . '/../vendor/autoload.php';

Bootstrap::setHandle();
$B = new Bootstrap(
    Configure::factory(
        '@PHP',
        DIR_CONFIG . '/publish',
        DIR_CONFIG . '/publish'
    ),
    'publish'
);

require __DIR__ . '/__init__.php';

$B->run();