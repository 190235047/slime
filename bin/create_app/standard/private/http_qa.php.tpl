<?php
require '__init__.php';

$CFG = \Slime\Component\Config\Configure::factory('@PHP');
$B = new \Slime\Bundle\Framework\Bootstrap($CFG);

$B->run(
    array()
);