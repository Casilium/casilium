<?php

declare(strict_types=1);

namespace Organisation\Handler;

use Organisation\Service\OrganisationManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;

class OrganisationReadHandler implements RequestHandlerInterface
{
    /**
     * @var OrganisationManager
     */
    protected $organisationManager;

    /**
     * @var TemplateRendererInterface
     */
    protected $renderer;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    public function __construct(
        OrganisationManager $organisationManager,
        TemplateRendererInterface $renderer,
        UrlHelper $urlHelper
    )
    {
        $this->organisationManager = $organisationManager;
        $this->renderer = $renderer;
        $this->urlHelper = $urlHelper;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $organisationId = $request->getAttribute('id');
        $organisation = $this->organisationManager->findOrganisationByUuid($organisationId);

        return new HtmlResponse($this->renderer->render('organisation::view', [
            'organisation' => $organisation,
        ]));
    }
}