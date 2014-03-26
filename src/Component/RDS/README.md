# 基本用法

```php
$M = new ModelFactory(
    array(
        'default' => array(
            'dsn'      => 'mysql:host=127.0.0.1;dbname=test',
            'username' => 'dbname',
            'password' => 'passwd',
            'options'  => array(
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_WARNING,
                \PDO::ATTR_TIMEOUT            => 3
            )
        )
    ),
    array(
        /*
        //default set:
        'Book' => array(
            'db' => 'default',
            'table' => 'book',
            'pk' => 'id',
            'fk' => 'book_id'
        )
        */
    )
);

$MB = $M->get('Book'); //table_name:book, pk:id
$Book = $MB->find(1);

$Books = $MB->findMulti(array(4,5,6));

$BooksList = $MB->findMulti(array('create_time >=' => '2014-01-01'), 'create_time DESC', 20, 10);

foreach ($BooksList as $OneBook) {
    echo $OneBook->belongsTo('Author');
}
*/
```

# 进阶1
```php
$M = new ModelFactory(
    array(
        'default' => array(
            'dsn'      => 'mysql:host=127.0.0.1;dbname=test',
            'username' => 'dbname',
            'password' => 'passwd',
            'options'  => array(
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_WARNING,
                \PDO::ATTR_TIMEOUT            => 3
            )
        )
    ),
    array(
        'Book' => array(
            'relation' => array(
                'Author' => 'belongsTo'
            )
        ),
        'Author' => array(
            'relation' => array(
                'Book' => 'hasMany'
            ),
        ),
    )
);

//Test1
$BooksList = $M->get('Book')->findMulti(array('create_time >=' => '2014-01-01'), 'create_time DESC', 20, 10);
foreach ($BooksList as $OneBook) {
    echo $OneBook->Author();
}

//Test2
$Author = $M->get('Author')->find(array('id' => 1));
echo $Author->Book();

*/
```

# 进阶2