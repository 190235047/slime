<?php
date_default_timezone_set('PRC');

define('DIR_BASE', dirname(__DIR__));
define('DIR_CLASS', DIR_BASE . '/class');
define('DIR_CONFIG', DIR_BASE . '/config');
define('DIR_I18N', DIR_BASE . '/i18n');
define('DIR_PRIVATE', DIR_BASE . '/private');
define('DIR_PUBLIC', DIR_BASE . '/public');
define('DIR_VIEW', DIR_BASE . '/view');

require DIR_BASE . '/vendor/autoload.php';

#debug
#/** @var Composer\Autoload\ClassLoader $AL */
#$AL = require DIR_BASE . '/../../../../vendor/autoload.php';
#$AL->addPsr4('AppSTD\\', DIR_CLASS);
