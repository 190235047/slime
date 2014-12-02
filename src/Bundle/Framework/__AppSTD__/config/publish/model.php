<?php
return array(
    '__MODEL__'   => [
        /*
        'User' => array(
            'db'                       => 'default',
            'table'                    => 'user',
            'pk'                       => 'id',
            'fk'                       => 'user_id',
            'use_full_field_in_select' => true,
            'fields'                   => array('id', 'name', 'password', 'create_time', 'last_update_time'),
            'relation'                 => array(
                'Clothes' => 'hasMany',
                'Mother'  => 'BelongsTo',
                'Book'    => 'hasManyThrough'
            )
        )
        */
    ],
    '__DEFAULT__' => [
        'own_config'  => true,
        'auto_create' => false,
        'db'          => 'default',
        'model_pre'   => '\\AppSTD\\Model\\M_',
        'item_pre'    => '\\AppSTD\\Model\\Item_',
        'model_base'  => '\\AppSTD\\System\\ORM\\Model',
        'item_base'   => '\\AppSTD\\System\\ORM\\Item'
    ]
);
