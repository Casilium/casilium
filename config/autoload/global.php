<?php

declare(strict_types=1);

return [
    'mfa'   => [
        'enabled' => true,
        'issuer'  => 'casilium.com',
    ],
    'cache' => [
        'namespace' => '',
        'ttl'       => 0,
        'directory' => './data/cache',
    ],
];
