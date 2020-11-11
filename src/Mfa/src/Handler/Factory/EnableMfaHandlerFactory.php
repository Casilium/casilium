<?php
declare(strict_types=1);

namespace Mfa\Handler\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Mfa\Handler;
use Psr\Container\ContainerInterface;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;

/**
 * Displays the form to enable MFA
 *
 * @package Mfa\Handler\Factory
 */
class EnableMfaHandlerFactory
{
    /**
     * @param ContainerInterface $container
     * @return Handler\EnableMfaHandler
     */
    public function __invoke(ContainerInterface $container): Handler\EnableMfaHandler
    {
        // get mfa config
        $config = $container->get('config')['mfa'] ?? [];

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get(EntityManagerInterface::class);

        /** @var TemplateRendererInterface $renderer */
        $renderer = $container->get(TemplateRendererInterface::class);

        /** @var UrlHelper $urlHelper */
        $urlHelper = $container->get(UrlHelper::class);
        return new Handler\EnableMfaHandler($entityManager, $renderer, $urlHelper, $config);
    }
}
