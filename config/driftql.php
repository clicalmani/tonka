<?php

return [
    /*
    |--------------------------------------------------------------------------
    | DRIFTQL BRIDGE ENABLED
    |--------------------------------------------------------------------------
    */
    'enabled' => env('DRIFTQL_BRIDGE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | DRIFTQL BRIDGE KEY
    |--------------------------------------------------------------------------
    | A secret key that the front-end must provide to access the DriftQL bridge.
    | This is an additional layer of security to prevent unauthorized access.
    */
    'bridge_public_key' => env('DRIFTQL_BRIDGE_PUBLIC_KEY', 'default_public_key'),

    /*
    |--------------------------------------------------------------------------
    | Model Whitelist
    |--------------------------------------------------------------------------
    | List of models (classes) that the front-end is allowed to query. 
    | If a model is not in this list, the query is rejected immediately.
    */
    'whitelist' => [
        'allowed_models' => [
            \App\Models\User::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 2. AUTHORIZATION (Policies & Row Level Security)
    |--------------------------------------------------------------------------
    | Defines access restrictions by Role and by Model. 
    | The backend should apply these "Global Scopes" automatically.
    |
    | Format :
    | 'ModelName' => [
    |      'RoleName' => [ Filtering rule ]
    | ]
    */
    'policies' => [
        \App\Models\User::class => [
            // Les admins voient tout (pas de filtre)
            'admin' => null, 
            
            // Les éditeurs ne peuvent pas voir les autres admins
            'editor' => [
                'column' => 'role',
                'operator' => '!=',
                'value' => 'admin', 
                // Note: 'value' can also be 'current_user_id' for dynamic values
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | HARDCODED LIMITATION (DoS Protection)
    |--------------------------------------------------------------------------
    | Strength of the ceilings to protect the database.
    */
    'limits' => [
        // Maximum number of records that the front end can request at one time
        'max_limit' => 100, 
        
        // Default number if the front does not specify a limit
        'default_limit' => 20,
        
        // Maximum number allowed for the offset (to avoid excessively deep pagination)
        'max_offset' => 10000,
    ],

    /*
    |--------------------------------------------------------------------------
    | SQL SANITIZATION
    |--------------------------------------------------------------------------
    | Security guidelines for constructing the request.
    */
    'security' => [
        // The use of "raw SQL" from the front end is strictly prohibited.
        // Only structured "where" clauses (column, operator, binding) are accepted.
        'allow_raw_sql' => false,

        // Checks that the columns requested in 'orderBy' or 'where' actually exist
        // in the database schema.
        'strict_column_check' => true,
    ],
];