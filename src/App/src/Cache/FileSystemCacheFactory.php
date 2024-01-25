<?php

declare(strict_types=1);

namespace App\Cache;

use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class FileSystemCacheFactory
{
    public function __invoke(ContainerInterface $container): AdapterInterface
    {
        $config    = $container->get('config')['cache'] ?? [];
        $directory = $config['directory'] ?? './data/cache';
        $ttl       = isset($config['ttl']) ? (int) $config['ttl'] : 3600;
        $namespace = $config['namespace'] ?? '';

        return new FilesystemAdapter($namespace, $ttl, $directory);
    }
}
