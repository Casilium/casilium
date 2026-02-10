<?php

declare(strict_types=1);

return [
    'tickets' => [
        // Number of days after resolution before tickets are auto-closed
        'auto_close_days' => 2,
        'pdf'             => [
            'cache_dir'      => './data/cache/dompdf',
            'remote_enabled' => false,
            'default_font'   => 'Helvetica',
            'dpi'            => 96,
            'chroot'         => './public',
            'paper'          => 'A4',
            'orientation'    => 'portrait',
            'logo_path'      => './public/img/casilium-black.svg',
        ],
        'reports'         => [
            'executive' => [
                'include_unresolved' => true,
                'unresolved_limit'   => 20,
                'unresolved_fields'  => [
                    'id',
                    'created',
                    'due',
                    'updated',
                    'type',
                    'contact',
                ],
            ],
        ],
        'csat'            => [
            'enabled'  => false,
            'base_url' => 'https://example.com/customer-satisfaction',
        ],
    ],
];
