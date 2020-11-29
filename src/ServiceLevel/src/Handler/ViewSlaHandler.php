<?php

declare(strict_types=1);

namespace ServiceLevel\Handler;

use Exception;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ServiceLevel\Service\SlaService;

class ViewSlaHandler implements RequestHandlerInterface
{
    /** @var SlaService */
    protected $slaService;

    /** @var TemplateRendererInterface */
    protected $renderer;

    /** @var UrlHelper */
    protected $urlHelper;

    public function __construct(
        SlaService $slaService,
        TemplateRendererInterface $renderer,
        UrlHelper $urlHelper
    ) {
        $this->slaService = $slaService;
        $this->renderer   = $renderer;
        $this->urlHelper  = $urlHelper;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $slaId = (int) $request->getAttribute('id');
        if ($slaId === 0) {
            throw new Exception('SLA not found');
        }

        $sla = $this->slaService->findSlaById($slaId);

        return new HtmlResponse($this->renderer->render('sla::view-sla', [
            'sla' => $sla,
        ]));
    }
}
