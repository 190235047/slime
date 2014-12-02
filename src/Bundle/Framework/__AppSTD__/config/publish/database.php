<?php
return [
    '__DB__'  => [
        'default' => [
            'master' => array(
                'dsn'      => 'mysql:host=127.0.0.1;dbname=test',
                'username' => 'root',
                'password' => '',
                'options'  => array(
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
                    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_WARNING,
                    \PDO::ATTR_TIMEOUT            => 3
                )
            )
        ]
    ],
    '__CB__'  => null,
    '__AOP__' => [
        'query.before,exec.before,prepare.before' => array(
            array('\\Slime\\Component\\RDBMS\\DBAL\\Ext', 'cbRunPDO')
        )
    ],
];
