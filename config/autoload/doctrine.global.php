<?php

declare(strict_types=1);

use App\Doctrine\UtcDateTimeType;
use Doctrine\Migrations\Configuration\Migration\ConfigurationLoader;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Ramsey\Uuid\Doctrine\UuidType;
use Roave\PsrContainerDoctrine\EntityManagerFactory;
use Roave\PsrContainerDoctrine\Migrations\ConfigurationLoaderFactory;
use Roave\PsrContainerDoctrine\Migrations\DependencyFactoryFactory;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

return [
    'dependencies' => [
        'aliases'   => [
            EntityManagerInterface::class => 'doctrine.entity_manager.orm_default',
        ],
        'factories' => [
            'doctrine.entity_manager.orm_default' => EntityManagerFactory::class,
            ConfigurationLoader::class            => ConfigurationLoaderFactory::class,
            DependencyFactory::class              => DependencyFactoryFactory::class,
        ],
    ],
    'doctrine'     => [
        'driver'        => [
            'orm_default' => [
                'class' => MappingDriverChain::class,
            ],
        ],
        'connection'    => [
            'orm_default' => [
                'charset'   => 'UTF8mb4',
                'collation' => 'UTF8mb4_unicode_ci',
            ],
        ],
        'configuration' => [
            'orm_default' => [
                'result_cache'       => 'array',
                'metadata_cache'     => 'array',
                'query_cache'        => 'array',
                'hydration_cache'    => 'array',
                'second_level_cache' => [
                    'enabled'                    => false,
                    'default_lifetime'           => 3600,
                    'default_lock_lifetime'      => 60,
                    'file_lock_region_directory' => '',
                    'regions'                    => [],
                ],
            ],
        ],
        'cache'         => [
            'array' => [
                'class' => ArrayAdapter::class,
            ],
        ],
        'migrations'    => [
            'orm_default' => [
                'table_storage'           => [
                    'table_name'                 => 'doctrine_migration_versions',
                    'version_column_name'        => 'version',
                    'version_column_length'      => 1024,
                    'executed_at_column_name'    => 'executed_at',
                    'execution_time_column_name' => 'execution_time',
                ],
                'migrations_paths'        => [
                    'DoctrineMigrations' => 'data/migrations',
                ],
                'all_or_nothing'          => true,
                'check_database_platform' => true,
            ],
        ],
        'types'         => [
            UuidType::NAME => UuidType::class,
            'utcdatetime'  => UtcDateTimeType::class,
        ],
    ],
];
