<?php

use App\Enums\UserRole;

return [
    /**
     * Control if the seeder should create a user per role while seeding the data.
     */
    'create_users' => false,

    /**
     * Control if all the laratrust tables should be truncated before running the seeder.
     */
    'truncate_tables' => true,

    'roles_structure' => [
        UserRole::SUPER_ADMIN->value => [
            'page' => 'p-da,p-ab,p-ac,p-no,p-se,p-rm,p-um',
            'action' => 'a-du,a-eu,a-au',
        ],
        UserRole::ADMIN->value => [
            'page' => 'p-da,p-ab,p-ac,p-no,p-se,p-um',
            'action' => 'a-eu',
        ],
        UserRole::USER->value => [
            'page' => 'p-da,p-ab,p-ac,p-no',
        ],
    ],

    'permissions_map' => [
        'p-da' => 'dashboard',
        'p-ab' => 'about',
        'p-ac' => 'account',
        'p-no' => 'notifications',
        'p-se' => 'settings',
        'p-rm' => 'roles-management',
        'p-um' => 'users-management',

        'a-du' => 'delete-user',
        'a-eu' => 'edit-user',
        'a-au' => 'add-user',
    ],
];
