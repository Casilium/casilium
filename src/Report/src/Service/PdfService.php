<?php

declare(strict_types=1);

namespace Report\Service;

use Carbon\CarbonInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Mezzio\Template\TemplateRendererInterface;
use Organisation\Entity\Organisation;

use function realpath;
use function sprintf;

class PdfService
{
    private string $fontCacheDir;

    private TemplateRendererInterface $renderer;

    /** @var array<string, mixed> */
    private array $options;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(TemplateRendererInterface $renderer, string $fontCacheDir, array $options = [])
    {
        $this->renderer     = $renderer;
        $this->fontCacheDir = $fontCacheDir;
        $this->options      = $options;
    }

    public function generateExecutiveReport(
        array $stats,
        Organisation $organisation,
        CarbonInterface $startDate,
        CarbonInterface $endDate,
    ): string {
        $logoPath         = $this->options['logo_path'] ?? './public/img/casilium-black.svg';
        $resolvedLogoPath = realpath($logoPath);
        if (false !== $resolvedLogoPath) {
            $logoPath = $resolvedLogoPath;
        }

        $html = $this->renderer->render('report::executive-report-pdf', [
            'layout'       => false,
            'stats'        => $stats,
            'organisation' => $organisation,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'logoPath'     => $logoPath,
        ]);

        return $this->renderPdf($html);
    }

    private function renderPdf(string $html): string
    {
        $chroot = $this->options['chroot'] ?? null;
        if (null === $chroot || '' === $chroot) {
            $chroot = sprintf('%s/../../../..', __DIR__);
        }

        $options = new Options();
        $options->setIsRemoteEnabled($this->options['remote_enabled'] ?? true);
        $options->setDefaultFont($this->options['default_font'] ?? 'Helvetica');
        $options->setDpi($this->options['dpi'] ?? 96);
        $options->setChroot($chroot);
        $options->setFontCache($this->fontCacheDir);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper(
            $this->options['paper'] ?? 'A4',
            $this->options['orientation'] ?? 'portrait'
        );
        $dompdf->render();

        return $dompdf->output();
    }
}
