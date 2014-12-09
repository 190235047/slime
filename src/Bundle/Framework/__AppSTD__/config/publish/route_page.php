<?php
return array(
    array(
        '__FILTERS__' => array(array('@matchHOST', 'app_std'), array('@isGET')),
        '__RE__' => '#^/hello/(.*)#',
        '__CB__' => function($REQ, $RESP, $CTX, $sP1) {
            /** @var \Slime\Component\Http\RESP $RESP */
            $RESP->setBody("Result: {$sP1}");
            return false;
        },
    ),
    array(
        '__CB__'    => array('\\Slime\\Component\\Route\\Mode', 'slimeHttp_Page'),
        '__PARAM__' => array(
            'default_controller' => 'Main',
            'default_action'     => 'Default',
            'controller_pre'     => '\\AppSTD\\C_Page\\C_',
            'action_pre'         => 'action'
        ),
    ),
);
