<?php

declare(strict_types=1);

namespace Mfa\Service\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Mfa\Service\MfaService;
use Mfa\Service\TotpService;
use Psr\Container\ContainerInterface;
use RuntimeException;

class MfaServiceFactory
{
    public function __invoke(ContainerInterface $container): MfaService
    {
        $entityManager = $container->get(EntityManagerInterface::class);
        $config        = $container->get('config');
        $mfaConfig     = $config['mfa'] ?? [];
        $encryptionKey = $config['encryption']['key'] ?? null;

        if ($encryptionKey === null) {
            throw new RuntimeException('Encryption key not configured.');
        }

        $totpService = $container->get(TotpService::class);

        return new MfaService($entityManager, $mfaConfig, $totpService, $encryptionKey);
    }
}
