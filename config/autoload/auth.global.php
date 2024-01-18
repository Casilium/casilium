<?php

declare(strict_types=1);

return [
    'authentication' => [
        'redirect' => '/',
    ],

    // The 'access_filter' key is used by the Auth module to restrict or permit access to certain
    // routes or actions for unauthorised visitors
    'access_filter' => [
        'options' => [
            // permissive or restrictive, restrictive by default
            'mode' => 'restrictive',
        ],
        'routes'  => [
            'admin'            => [
                ['allow' => '@'],
            ],
            'admin.user'       => [
                ['allow' => '+user.manage'],
            ],
            'admin.role'       => [
                ['allow' => '+role.manage'],
            ],
            'admin.permission' => [
                ['allow' => '+permission.manage'],
            ],

            // give authenticated users access to the home page
            'home' => [
                ['allow' => '@'],
            ],
            // give everyone access to the login page
            'login' => [
                ['allow' => '*'],
            ],
            // give authenticated users access to the logout page
            'logout' => [
                ['allow' => '@'],
            ],
            'mfa'    => [
                ['allow' => '*'],
            ],
        ],
    ],
];
