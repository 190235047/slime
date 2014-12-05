<?php
use Slime\Component\Log\Logger;

return array(
    'Log'   => array(
        'class'  => 'Slime\\Component\\Log\\Logger',
        'params' => array(
            array(
                'File' => array('@File', '/tmp/php_AppSTD_api_{level}_{date}.log')
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
        'params'       => array(':HttpRequest', DIR_I18N),
        'factory'      => 'createFromHttp',
        'parse_params' => true
    ),
    'Event' => array(
        'class'  => 'Slime\\Component\\Event\\Event',
        'params' => array()
    ),
);
