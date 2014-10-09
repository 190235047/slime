<?php
require 'DAL\SQL.php';
require 'DAL\SQL_SELECT.php';
require 'DAL\SQL_DELETE.php';
require 'DAL\SQL_UPDATE.php';
require 'DAL\SQL_INSERT.php';
require 'DAL\Val.php';
require 'DAL\Bind.php';
require 'DAL\Engine.php';

use \Slime\Component\RDBMS\DAL\Val;


$SEL = \Slime\Component\RDBMS\DAL\SQL::R('abc');
$B = $SEL->getBind();

$B
    ->set('status', 1, PDO::PARAM_INT)
    ->set('name', 'abc')
    ->set('aaa', 'xxxxx');

$SEL
    ->fields('title', 'id', 'create_time')
    ->where(
        [
            'status' => $B['status'],
            'create_time >=' => '2014-05-01 20:00:01',
            Val::K_OP_V(Val::Name('a'), $B['aaa'], 'LIKE'),
            Val::K_OP_V('bbb', Val::Name('title')),
            Val::K_OP_V(Val::Val('ccc'), 'ccc'),
            Val::K_OP_V(1, 1),
            [
                'q LIKE' => "%{$B['name']}%",
                'b IN' => array('4',5,'aa')
            ],
            'ttt NOT IN' => array(4,3,5),
            -1 => 'OR'
        ]
    )->orderBy('create_time', '-id', '+status')
    ->groupBy('abc', 'def')
    ->limit(10)
    ->offset(30);


printf("%s\n", $SEL);

printf("%s\n", \Slime\Component\RDBMS\DAL\SQL::D('abc')->where(['id' => 5, 'title' => 'abc']));

printf(
    "%s\n",
    \Slime\Component\RDBMS\DAL\SQL::U('abc')->where(['id' => 5, 'title' => 'abc'])
    ->setKV(['a' => 'xxx', 'b' => 5, 'c' => Val::Name('b')])
    ->limit(1)
);

printf(
    "%s\n",
    \Slime\Component\RDBMS\DAL\SQL::C('abc')->addData(
        array('f1' => 'a', 'f2' => 't', 'fc' => Val::Name('b'))
    )
);