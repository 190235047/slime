<?php
use Slime\Component\Log\Logger;

return array(
    'Log'   => array(
        'class'  => 'Slime\\Component\\Log\\Logger',
        'params' => array(
            array(
                'WebPage' => array('@WebPage'),
                'File'    => array('@File', '/tmp/php_AppSTD_http_{level}_{date}.log', 50)
            ),
            Logger::LEVEL_ALL ^ Logger::LEVEL_DEBUG
        ),
    ),
    'View'  => array(
        'class'  => 'Slime\\Component\\View\\Adaptor_PHP',
        'params' => array(DIR_VIEW)
    ),
    'ORM'   => array(
        'class'        => 'AppSTD\\System\\ORM\\Factory',
        'creator'      => 'createFromConfig',
        'params'       => array('@database', '@model'),
        'parse_params' => true
    ),
    'I18N'  => array(
        'class'        => 'Slime\\Component\\I18N\\I18N',
        'creator'      => 'createFromHttp',
        'params'       => array(':REQ', DIR_I18N),
        'parse_params' => true
    ),
    'Event' => array(
        'class'  => 'Slime\\Component\\Event\\Event',
        'params' => array()
    ),
);
