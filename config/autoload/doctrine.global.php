<?php
declare(strict_types=1);

use Doctrine\ORM\EntityManagerInterface;
use ContainerInteropDoctrine\EntityManagerFactory;
use Ramsey\Uuid\Doctrine\UuidType;

return [
    'dependencies' => [
        'factories' => [
            'doctrine.entity_manager.orm_default' => \ContainerInteropDoctrine\EntityManagerFactory::class,
        ],
    ],
    'doctrine' => [
        'driver' => [
            'orm_default' => [
                'class' => \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain::class,
                'drivers' => [
                ],
            ],
        ],
        'cache' => [
            'apcu' => [
                'class' => \Doctrine\Common\Cache\ApcuCache::class,
                'namespace' => 'container-interop-doctrine',
            ],
            'array' => [
                'class' => \Doctrine\Common\Cache\ArrayCache::class,
                'namespace' => 'container-interop-doctrine',
            ],
            'filesystem' => [
                'class' => \Doctrine\Common\Cache\FilesystemCache::class,
                'directory' => 'data/cache/DoctrineCache',
                'namespace' => 'container-interop-doctrine',
            ],
            'chain' => [
                'class' => \Doctrine\Common\Cache\ChainCache::class,
                'providers' => ['array', 'apcu'],
                'namespace' => 'container-interop-doctrine',
            ],
        ],
        // migrations configuration
        'migrations_configuration' => [
            'orm_default' => [
                'directory' => 'data/Migrations',
                'name' => 'Doctrine Database Migrations',
                'namespace' => 'Migrations',
                'table' => 'migrations',
            ],
        ],

        'configuration' => [
            'orm_default' => [
                'result_cache' => 'array',
                'metadata_cache' => 'array',
                'query_cache' => 'array',
                'hydration_cache' => 'array',
                'second_level_cache' => [
                    'enabled' => false,
                    'default_lifetime' => 3600,
                    'default_lock_lifetime' => 60,
                    'file_lock_region_directory' => '',
                    'regions' => [],
                ],
            ],
        ],
        'types' => [
            UuidType::NAME => UuidType::class,
            'utcdatetime' => \App\Doctrine\UtcDateTimeType::class,
        ],
    ],
];
