<?php
require '__init__.php';

$CFG = \Slime\Component\Config\Configure::factory('@PHP', DIR_CONFIG . '/publish');
$CTX = \Slime\Component\Support\Context::create($CFG, 'module');
$CTX->bindMulti(
    array(
        'Router' => $Router = new \Slime\Component\Route\Router(),
        'Config' => $CFG,
    )
);
$Router->addConfig($CFG->get('route'));
$B = new \Slime\Bundle\Framework\Bootstrap($CTX);
$B->run($Router);