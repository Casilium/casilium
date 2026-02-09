<?php

declare(strict_types=1);

namespace Report\Service\Factory;

use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Report\Service\PdfService;

class PdfServiceFactory
{
    public function __invoke(ContainerInterface $container): PdfService
    {
        $renderer     = $container->get(TemplateRendererInterface::class);
        $config       = $container->get('config');
        $pdfConfig    = $config['tickets']['pdf'] ?? [];
        $fontCacheDir = $pdfConfig['cache_dir'] ?? './data/cache/dompdf';

        return new PdfService($renderer, $fontCacheDir, $pdfConfig);
    }
}
