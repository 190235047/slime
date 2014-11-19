<?php
return array(
    array(
        'callback' => array('\\Slime\\Component\\Route\\Mode', 'slimeHttp_Page'),
        'setting'  => array(
            'default_controller' => 'Main',
            'default_action' => 'Default',
            'controller_pre' => '\\{{{NS}}}\\ControllerHTTP\\C_',
            'action_pre' => 'action'
        ),
    ),
);
