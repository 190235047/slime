<?php
use Slime\Component\Log\Logger;

return array(
    'Log'      => array(
        'class'  => 'Slime\\Component\\Log\\Logger',
        'params' => array(
            array(
                'File' => array('@File', '/tmp/php_AppSTD_cli_{level}_{date}.log', 50)
            ),
            Logger::LEVEL_ALL ^ Logger::LEVEL_DEBUG
        ),
    ),
    'ORM'      => array(
        'class'        => 'AppSTD\\System\\ORM\\Factory',
        'creator'      => 'createFromConfig',
        'params'       => array('@database', '@model'),
        'parse_params' => true
    ),
    'I18N'     => array(
        'class'        => 'Slime\\Component\\I18N\\I18N',
        'creator'      => 'createFromCli',
        'params'       => array(':aArgv', DIR_I18N),
        'parse_params' => true
    ),
    'Event'    => array(
        'class'  => 'Slime\\Component\\Event\\Event',
        'params' => array()
    ),
    'HttpCall' => array(
        'class'        => 'Slime\\Component\\Http\\Call',
        'params'       => array(3000, 3000, ':Event'),
        'parse_params' => true
    ),
);
