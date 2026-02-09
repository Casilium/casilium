<?php

declare(strict_types=1);

return [
    'tickets' => [
        // Number of days after resolution before tickets are auto-closed
        'auto_close_days' => 2,
        'csat'            => [
            'enabled'  => false,
            'base_url' => 'https://example.com/customer-satisfaction',
        ],
    ],
];
