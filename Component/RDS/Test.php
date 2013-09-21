<?php
require 'CURD.php';
require 'Model/Model.php';
require 'Model/Group.php';
require 'Model/Item.php';
require 'Model/Pool.php';

require '../Log/Logger.php';
require '../Log/IWriter.php';
require '../Log/Writer/STDFD.php';

$aDBConfig = array(
    'default' => array(
        'dsn'      => 'mysql:host=172.17.181.135;dbname=slime',
        'username' => 'root',
        'password' => 'entsafe',
        'options'  => array(
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_WARNING,
            \PDO::ATTR_TIMEOUT            => 3
        )
    )
);
$aModelConfig = array(
    'Book' => array(
        'db'    => 'default',
        'table' => 'book',
        'pk'    => 'id',
        'fk'    => 'book_id',
        'rel'   => array(
            'Owner' => 'belongsTo'
        ),
    ),

    'Owner' => array(
        'db'    => 'default',
        'table' => 'owner',
        'pk'    => 'id',
        'fk'    => 'owner_id',
        'rel'   => array(
            'Book' => 'hasMany'
        ),
    )
);

$Pool = new \SlimeFramework\Component\RDS\Model_Pool(
    $aDBConfig,
    $aModelConfig,
    new \SlimeFramework\Component\Log\Logger(array(new \SlimeFramework\Component\Log\Writer_STDFD()))
);
/*
$EGBook = $Pool->get('Book');
$Book = $EGBook->find(1);
$Owner = $Book->rel('Owner');

echo $Book . "\n";
echo $Owner . "\n";
*/

$ModelOwner = $Pool->get('Owner');
$Owner = $ModelOwner->find(1);
/** @var \SlimeFramework\Component\RDS\Model_Item[] $aBook */
$aBook = $Owner->rel('Book');

foreach ($aBook as $Book) {
    echo $Book . "\n";
}
echo $Owner . "\n";