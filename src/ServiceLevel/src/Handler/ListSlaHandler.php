<?php

declare(strict_types=1);

namespace ServiceLevel\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ServiceLevel\Service\SlaService;

class ListSlaHandler implements RequestHandlerInterface
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
        $policies = $this->slaService->findAllSlaPolicies();

        return new HtmlResponse($this->renderer->render('sla::list-sla', [
            'policies' => $policies,
        ]));
    }

    public function populateBusinessHours(): array
    {
        $result = $this->slaService->findAllBusinessHours();

        $businessHours = [];
        foreach ($result as $businessHour) {
            $businessHours[$businessHour->getId()] = $businessHour->getName();
        }
        return $businessHours;
    }
}
