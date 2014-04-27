<?php
namespace {{{NS}}};

use Slime\Bundle\Framework\Bootstrap;
use Slime\Component\Config\Configure;

date_default_timezone_set('{{{TIME_ZONE}}}');
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