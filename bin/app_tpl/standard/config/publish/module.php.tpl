<?php
use Slime\Component\Log\Logger;

return array(
    array(
        'module'   => 'Log',
        'class'    => 'Slime\\Component\\Log\\Logger',
        'params'   => array(
            array('@File' => array('/tmp/{{{APP_NAME}}}_cli_%s_%s.log')),
            Logger::LEVEL_ALL ^ Logger::LEVEL_DEBUG
        ),
        'run_mode' => 'cli'
    ),
    array(
        'module'   => 'Log',
        'class'    => 'Slime\\Component\\Log\\Logger',
        'params'   => array(
            array('@File' => array('/tmp/{{{APP_NAME}}}_http_%s_%s.log')),
            Logger::LEVEL_ALL ^ Logger::LEVEL_DEBUG
        ),
        'run_mode' => 'http'
    ),
    array(
        'module' => 'Event',
        'class'  => 'Slime\\Component\\Context\\Event',
        'params' => array()
    ),
    array(
        'module' => 'View',
        'class'  => 'Slime\\Component\\View\\Adaptor_PHP',
        'params' => array(DIR_VIEW)
    ),
    array(
        'module' => 'ModelFactory',
        'class'  => '{{{NS}}}\\System\\Model\\Factory_Base',
        'params' => array(
            '@database',
            '@model',
            '{{{NS}}}\\Model',
            '{{{NS}}}\\System\\Model\\Model_Base',
            \Slime\Component\RDS\AopPDO::$aAopPreExecCost
        )
    ),
    array(
        'module' => 'Redis',
        'class'  => 'Slime\\Component\\Redis\\Redis',
        'params' => array('@redis'),
    ),
    array(
        'module' => 'Lock',
        'class'  => 'Slime\\Component\\Lock\\Adaptor_Redis',
        'params' => array(':Redis')
    ),
    array(
        'module' => 'I18N',
        'class'  => 'Slime\\Component\\I18N\\I18N',
        'params' => array(
            DIR_LANGUAGE,
            ':HttpRequest',
            'english',
            'lang'
        ),
        'factory' => 'createFromHttp'
    ),
);