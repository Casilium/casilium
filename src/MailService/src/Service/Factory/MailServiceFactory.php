<?php

declare(strict_types=1);

namespace MailService\Service\Factory;

use MailService\Service\MailService;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class MailServiceFactory
{
    public function __invoke(ContainerInterface $container): MailService
    {
        $config   = $container->get('config')['mail'] ?? [];
        $renderer = $container->get(TemplateRendererInterface::class);
        $logger   = $container->has(LoggerInterface::class)
            ? $container->get(LoggerInterface::class)
            : null;

        return new MailService($renderer, $config, $logger);
    }
}
