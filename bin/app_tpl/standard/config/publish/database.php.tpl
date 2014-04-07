<?php
return array(
    'default' => array(
        'dsn'      => 'mysql:host=127.0.0.1;port=3306;dbname=test',
        'username' => 'root',
        'password' => '',
        'options'  => array(
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_WARNING,
            \PDO::ATTR_TIMEOUT            => 3
        )
    )
);
