<?php

declare(strict_types=1);

namespace ServiceLevel\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ServiceLevel\Service\SlaService;

class DeleteBusinessHoursHandler implements RequestHandlerInterface
{
    /** @var SlaService */
    protected $slaService;

    /** @var TemplateRendererInterface */
    protected $renderer;

    /** @var UrlHelper */
    protected $urlHelper;

    public function __construct(SlaService $slaService, TemplateRendererInterface $renderer, UrlHelper $urlHelper)
    {
        $this->slaService = $slaService;
        $this->renderer   = $renderer;
        $this->urlHelper  = $urlHelper;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id            = (int) $request->getAttribute('id');
        $businessHours = $this->slaService->findBusinessHoursById($id);
        $confirm       = (bool) $request->getAttribute('confirm');

        if (true === $confirm) {
            $this->slaService->deleteBusinessHours($id);
            return new RedirectResponse($this->urlHelper->generate('sla.list_business_hours'));
        }

        return new HtmlResponse($this->renderer->render('sla::delete-business-hours', [
            'businessHours' => $businessHours,
        ]));
    }
}
