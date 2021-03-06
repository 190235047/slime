<?php
use Slime\Component\Log\Logger;

return array(
    'Log'   => array(
        'class'  => 'Slime\\Component\\Log\\Logger',
        'params' => array(
            array(
                'WebPage' => array('@WebPage'),
                'File'    => array('@File', '/tmp/php_AppSTD_page_{level}_{date}.log', 0)
            ),
            Logger::LEVEL_ALL
        )
    ),
    'View'  => array(
        'class'        => 'Slime\\Component\\View\\Adaptor_PHP',
        'params'       => array(DIR_VIEW, ':Event'),
        'parse_params' => true
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
    'Pagination' => array(
        'class' => 'Slime\\Component\\RDBMS\\ORM\\Pagination',
        'params' => array(':REQ', 5),
        'parse_params' => true
    ),
);
