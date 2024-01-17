<?php

declare(strict_types=1);

use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Version\Comparator;
use Doctrine\Migrations\Version\Version;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Laminas\ServiceManager\ServiceManager;

require dirname(__DIR__) . '/vendor/autoload.php';

/** @var ServiceManager $container */
$container = require __DIR__ . '/container.php';

// @codingStandardsIgnoreStart
global $argv;
// @codingStandardsIgnoreEnd
if (str_contains($argv[0], 'doctrine-migrations')) {
    /** @var DependencyFactory $factory */
    $factory = $container->get(DependencyFactory::class);
    $factory->setService(Comparator::class, new class () implements Comparator {
        public function compare(Version $a, Version $b): int
        {
            return strcmp(self::versionWithoutNamespace($a), self::versionWithoutNamespace($b));
        }

        private static function versionWithoutNamespace(Version $version): string
        {
            $parsed = strrchr($version->__toString(), '\\');
            if ($parsed === false) {
                throw new RuntimeException('Unable to parse version ' . $version->__toString());
            }

            return $parsed;
        }
    });

    return $factory;
}

/** @var EntityManager $entityManager */
$entityManager = $container->get(EntityManager::class);
return new SingleManagerProvider($entityManager);
