<?php
return array(
    array(
        '__FILTERS__' => array('@isPOST'),
        '__RE__' => '#^/hello/(.*)#',
        '__CB__' => function($REQ, $RESP, $CTX, $sP1) {
            $RESP->setBody("ResultPOST: {$sP1}");
            return false;
        },
    ),

    array(
        '__FILTERS__' => array('@isGET'),
        '__RE__' => '#^/hello/(.*)#',
        '__CB__' => function($REQ, $RESP, $CTX, $sP1) {
            $RESP->setBody("ResultGET: {$sP1}");
            return false;
        },
    ),

    array(
        '__CB__'    => array('\\Slime\\Component\\Route\\Mode', 'slimeHttp_Page'),
        '__PARAM__' => array(
            'default_controller' => 'Main',
            'default_action'     => 'Default',
            'controller_pre'     => '\\SlimeCMS\\ControllerHTTP\\C_',
            'action_pre'         => 'action'
        ),
    ),
);
