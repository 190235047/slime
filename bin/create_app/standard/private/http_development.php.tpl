<?php
require '__init__.php';

$CFG = \Slime\Component\Config\Configure::factory('@PHP', DIR_CONFIG . '/development');
$CTX = \Slime\Component\Support\Context::create($CFG, 'module');
$B   = new \Slime\Bundle\Framework\Bootstrap($CFG, $CTX);
$CTX->callIgnore('SYSTEM_RUN_BEFORE');
$B->run($CFG->get('route'), $CTX->Log);
