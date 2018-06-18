<?php
  
  return [
    'config' => [
      /**
       *  'config' need var permissions with roles rules
       *  'DB' need var db_table there with table name. Table where store roles in formate like in var permissions
       */
      'role_source' => 'config' // 'config' || 'DB'
      
      /**
       * var for 'role_source' => 'DB', value is table where store kit for roles
       */
      //      'db_table' => 'permissions'
    ],
    
    /**
     *  var for 'role_source' => 'config', value is kit for roles (like rows from DB)
     */
    'permissions' => [
      'admin' => (object) [
        'role' => 'admin',
        'type' => 'all allow', // or 'all deny'
        'list' => [] // if in table - need in json formate
      ],
      'manager' => (object) [
        'role' => 'manager',
        'type' => 'all deny',
        'list' => [
          'guests.*',
          'someother.index',
          'someother.update'
        ]
      ]
    ]
  ];