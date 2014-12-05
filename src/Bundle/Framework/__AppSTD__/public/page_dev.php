<?php
require '__init__.php';

# set error handle if you need
set_error_handler(array('\\Slime\\Bundle\\Framework\\Ext', 'hError'), E_ALL | E_STRICT);

# init object
$CFG    = \Slime\Component\Config\Configure::factory('@PHP', DIR_CONFIG . '/development', DIR_CONFIG . '/publish');
$CTX    = \AppSTD\System\Support\CTX::create($CFG, 'module_page');
$Router = new \Slime\Component\Route\Router();
$Router->addConfig((array)$CFG->get('route_page'));

# bind if you need
$CTX->bindMulti(array('Config' => $CFG, 'Router' => $Router));
$CTX->bindCB('__Uncaught__', array('\\Slime\\Bundle\\Framework\\Ext', 'hUncaught'));

# register event if you need
//$Ev  = $CTX->Event;
//$Log = $CTX->Log;
//\Slime\Component\Http\Ext::ev_LogCost($Ev, $Log);
//\Slime\Component\NoSQL\Redis\Ext::ev_LogCost($Ev, $Log);
//\Slime\Component\NoSQL\Memcached\Ext::ev_LogCost($Ev, $Log);
//\Slime\Component\View\Ext::ev_LogPHPRender($Ev, $Log);

# run
\Slime\Bundle\Framework\Bootstrap::run($Router, $CTX);
