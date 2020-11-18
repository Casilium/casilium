<?php

declare(strict_types=1);

namespace OrganisationSite\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Organisation\Exception\OrganisationNotFoundException;
use OrganisationSite\Service\SiteManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ListSiteHandler implements RequestHandlerInterface
{
    /** @var SiteManager */
    protected $siteManager;

    /** @var TemplateRendererInterface */
    protected $renderer;

    /** @var UrlHelper */
    protected $urlHelper;

    public function __construct(SiteManager $siteManager, TemplateRendererInterface $renderer, UrlHelper $urlHelper)
    {
        $this->siteManager = $siteManager;
        $this->renderer    = $renderer;
        $this->urlHelper   = $urlHelper;
    }

    /**
     * Handle requests for organisation site list
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // grab organisation uuid from url vars
        $org_id = $request->getAttribute('id');

        // find the related organisation
        $organisation = $this->siteManager->getOrganisationByUuid($org_id);
        if (null === $organisation) {
            throw OrganisationNotFoundException::whenSearchingByUuid($org_id);
        }

        $sites = $this->siteManager->fetchSitesByOrganisationId($organisation->getId());

        return new HtmlResponse($this->renderer->render('site::list-sites', [
            'sites'        => $sites,
            'organisation' => $organisation,
        ]));
    }
}
