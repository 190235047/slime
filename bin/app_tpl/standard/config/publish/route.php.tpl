<?php
return array(
    'http' => array(
        '#^/v\d+/#' => array(array('Slime\Component\Route\Mode', 'slimeHttp_REST'), '{{{NS}}}\\ControllerApi\\C_', ''),
        array(array('Slime\Component\Route\Mode', 'slimeHttp_Page'), '{{{NS}}}\\ControllerHttp\\C_', 'action')
    ),
    'cli'  => array(
        array(array('Slime\Component\Route\Mode', 'slimeCli'), '{{{NS}}}\\ControllerApi\\C_', 'action')
    )
);