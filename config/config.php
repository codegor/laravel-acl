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
          'guests.*', // guest.* - это в том числе и меню, а если нужно части, то нужно еще вставлять guests._menu
          'properties._menu',
          'agreements.index'
        ]
      ]
    ],
    'state' => [
      'admins' => [ // resourse
        'active' =>[ // status #1
          'activate'
        ],
        'inactive' =>[ // status #2
          'update'
        ],
      ],
      'agreements' => [
        'active' =>[
          'activate',
          'update',
          'destroy'
        ],
        'draft' =>[
        ],
      ],
      'guests' => [
        'signed' =>[
          'activate',
          ['update', ['admin']],// if array: 1 item - it is rule name (resourse action), 2 item - it is role name for which will be canceled this rule
          'destroy'
        ],
        'draft' =>[
        ],
      ],
    ]
    /**
    'state' => [
      'admins' => [ // resourse
        '_field' => 'model.status', // field for vue (def = model.status)
        '_model_field' => 'status' // field for laravel model (def = status)
        '_model' => 'admin' // laravel model (def = resourses without last s)
        '_statuses' => [
          'active' => [
            '_type' => 'all allow', // or can be 'all deny' (def = 'all allow')
            '_list' => [ // status #1
              'activate',
              ['update', ['admin']] // if array: 1 item - it is rule name (resourse action), 2 item - it is role name for which will be canceled this rule
              ...
            ],
          ],
          'inactive' => [ // status #2
            .....
          ],
        ]
      ],
    ]
    
    or one else variant

    'state' => [
      'admins' => [ // resourse
        [
          '_field' => 'model.status', // field for vue (def = model.status)
          '_model_field' => 'status' // field for laravel model (def = status)
          '_statuses' => [
            'active' => [
              '_type' => 'all allow', // or can be 'all deny' (def = 'all allow')
              '_list' => [ // status #1
                'activate',
                ['update', ['admin']] // if array: 1 item - it is rule name (resourse action), 2 item - it is role name for which will be canceled this rule
                ...
              ],
            ],
            'inactive' => [ // status #2
              .....
            ],
          ]
        ],
        [
          '_field' => '__other field__', // field for vue
          '_model_field' => '__other field__' // field for laravel model
          '_statuses' => [
            'active' => [
              '_type' => 'all allow', // or can be 'all deny' (def = 'all allow')
              '_list' => [ // status #1
                'activate',
                ['update', ['admin']] // if array: 1 item - it is rule name (resourse action), 2 item - it is role name for which will be canceled this rule
                ...
              ],
            ],
            'inactive' => [ // status #2
              .....
            ],
          ]
        ],
        [ // or short record
          'active' =>[ // status #1 (field for model laravel  and for vue getted from def value, type getted by def - 'all allow' _ignore_for_role def is empty)
            'activate'
          ],
          ...
        ]
        [
          ...
        ]
      ]
    ]
  */
  ];