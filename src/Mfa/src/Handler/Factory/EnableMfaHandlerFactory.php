<?php
declare(strict_types=1);

namespace Mfa\Handler\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Mfa\Handler;
use Psr\Container\ContainerInterface;

/**
 * Displays the form to enable MFA
 */
class EnableMfaHandlerFactory
{
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
