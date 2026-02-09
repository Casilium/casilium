<?php

declare(strict_types=1);

namespace Mfa\Service\Factory;

use Mfa\Service\TotpService;
use Psr\Container\ContainerInterface;

class TotpServiceFactory
{
    public function __invoke(ContainerInterface $container): TotpService
    {
        $config    = $container->get('config')['mfa']['totp'] ?? [];
        $digits    = (int) ($config['digits'] ?? 6);
        $period    = (int) ($config['period'] ?? 30);
        $window    = (int) ($config['window'] ?? 1);
        $algorithm = (string) ($config['algorithm'] ?? 'sha1');

        return new TotpService($digits, $period, $window, $algorithm);
    }
}
