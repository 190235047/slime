<?php
namespace {{{NS}}};

use Slime\Bundle\Framework\Bootstrap;
use Slime\Component\Config\Configure;

require __DIR__ . '/_common.php';

Bootstrap::setHandle();
$B = new Bootstrap(
    Configure::factory(
        '@PHP',
        DIR_CONFIG . '/development',
        DIR_CONFIG . '/publish'
    ),
    'development'
);
Bootstrap::setDEVErrorPage();

$B->run();