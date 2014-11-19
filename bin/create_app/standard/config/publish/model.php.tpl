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
        'model_pre'   => '\\{{{NS}}}\\Model\\M_',
        'item_pre'    => '\\{{{NS}}}\\Model\\Item_',
        'model_base'  => '\\{{{NS}}}\\System\\ORM\\Model',
        'item_base'   => '\\{{{NS}}}\\System\\ORM\\Item'
    ]
);
