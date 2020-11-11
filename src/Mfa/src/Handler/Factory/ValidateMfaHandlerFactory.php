<?php
declare(strict_types=1);

namespace Mfa\Handler\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Mfa\Handler;
use Psr\Container\ContainerInterface;
use Laminas\Cache\Storage\StorageInterface;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;

/**
 * Displays the MFA form for validation after user login
 *
 * @package Mfa\Handler\Factory
 */
class ValidateMfaHandlerFactory
{
    /**
     * @param ContainerInterface $container
     * @return Handler\ValidateMfaHandler
     */
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
