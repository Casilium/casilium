<?php

declare(strict_types=1);

namespace App\View\Helper\Factory;

use App\View\Helper\LocalDate;
use Psr\Container\ContainerInterface;

class LocalDateFactory
{
    public function __invoke(ContainerInterface $container): LocalDate
    {
        $config   = $container->get('config');
        $timezone = $config['locale']['timezone'] ?? 'Europe/London';
        $format   = $config['locale']['format'] ?? 'd/m/Y H:i:s';

        return new LocalDate($timezone, $format);
    }
}
