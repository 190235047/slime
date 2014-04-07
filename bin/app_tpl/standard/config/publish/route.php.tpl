<?php
return array(
    'http' => array(
        array(array('Slime\Component\Route\Mode', 'slimeHttp'), '{{{NS}}}\\ControllerHTTP\\C_', 'action')
    ),
    'cli'  => array(
        array(array('Slime\Component\Route\Mode', 'slimeCli'), '{{{NS}}}\\ControllerHTTP\\C_', 'action')
    )
);