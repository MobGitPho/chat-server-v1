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
            'page' => 'p-da,p-ab,p-ac,p-no,p-se,p-rm,p-um,p-me',
            'action' => 'a-du,a-eu,a-au,a-me,d-me,e-me',
        ],
        UserRole::ADMIN->value => [
            'page' => 'p-da,p-ab,p-ac,p-no,p-se,p-um,p-me',
            'action' => 'a-eu,a-me,d-me,e-me',
        ],
        UserRole::USER->value => [
            'page' => 'p-da,p-ab,p-ac,p-no,p-me',
            'actiion' => 'a-me,d-me,e-me'
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
        'p-me' => 'message',

        'a-du' => 'delete-user',
        'a-eu' => 'edit-user',
        'a-au' => 'add-user',
        'a-me' => 'add-message',
        'd-me' => 'delete-message',
        'e-me' => 'edit-message',
    ],
];
