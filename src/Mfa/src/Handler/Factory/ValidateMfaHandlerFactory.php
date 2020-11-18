<?php
declare(strict_types=1);

namespace Mfa\Handler\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Laminas\Cache\Storage\StorageInterface;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Mfa\Handler;
use Psr\Container\ContainerInterface;

/**
 * Displays the MFA form for validation after user login
 */
class ValidateMfaHandlerFactory
{
    public function __invoke(ContainerInterface $container): Handler\ValidateMfaHandler
    {
        // get mfa config
        $config = $container->get('config')['mfa'] ?? [];

        /** @var StorageInterface $cache */
        $cache = $container->get(StorageInterface::class);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get(EntityManagerInterface::class);

        /** @var TemplateRendererInterface $renderer */
        $renderer = $container->get(TemplateRendererInterface::class);

        /** @var UrlHelper $urlHelper */
        $urlHelper = $container->get(UrlHelper::class);
        return new Handler\ValidateMfaHandler($cache, $entityManager, $renderer, $urlHelper, $config);
    }
}
