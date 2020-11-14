<?php

declare(strict_types=1);

namespace OrganisationSite\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use OrganisationSite\Exception\SiteNotFoundException;
use OrganisationSite\Service\SiteManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ViewSiteHandler implements RequestHandlerInterface
{
    /**
     * @var SiteManager
     */
    protected $siteManager;

    /**
     * @var TemplateRendererInterface
     */
    protected $renderer;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    /**
     * ViewSiteHandler constructor.
     *
     * @param SiteManager $siteManager
     * @param TemplateRendererInterface $renderer
     * @param UrlHelper $urlHelper
     */
    public function __construct(SiteManager $siteManager, TemplateRendererInterface $renderer, UrlHelper $urlHelper)
    {
        $this->siteManager = $siteManager;
        $this->renderer = $renderer;
        $this->urlHelper = $urlHelper;
    }

    /**
     * Render HTML
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $siteId = $request->getAttribute('id');
        $site = $this->siteManager->fetchSiteByUuid($siteId);
        if (null === $site) {
            throw SiteNotFoundException::whenSearchingByUuid($siteId);
        }

        return new HtmlResponse($this->renderer->render('site::view-site', ['site' => $site]));
    }
}