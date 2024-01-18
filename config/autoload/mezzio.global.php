<?php

declare(strict_types=1);

use Laminas\Cache\Storage\Adapter\Apcu;
use Laminas\Cache\Storage\Adapter\Filesystem;
use Laminas\ConfigAggregator\ConfigAggregator;

return [
    // Toggle the configuration cache. Set this to boolean false, or remove the
    // directive, to disable configuration caching. Toggling development mode
    // will also disable it by default; clear the configuration cache using
    // `composer clear-config-cache`.
    ConfigAggregator::ENABLE_CACHE => true,

    // Enable debugging; typically used to provide debugging information within templates.
    'debug'  => false,
    'mezzio' => [
        // Provide templates for the error handling middleware to use when
        // generating responses.
        'error_handler' => [
            'template_404'   => 'error::404',
            'template_error' => 'error::error',
        ],
    ],
    'caches' => [
        'FileSystemCache' => [
            'adapter' => Filesystem::class,
            'options' => [
                'cache_dir' => './data/cache',
                'ttl'       => 60 * 60 * 1,
            ],
        ],
        'ApcCache'        => [
            'adapter' => Apcu::class,
        ],
    ],
];
