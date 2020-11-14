<?php

declare(strict_types=1);

namespace OrganisationSite\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Flash\FlashMessagesInterface;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use OrganisationSite\Entity\SiteEntity;
use OrganisationSite\Exception\SiteNotFoundException;
use OrganisationSite\Service\SiteManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DeleteSiteHandler implements RequestHandlerInterface
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
     * DeleteSiteHandler constructor.
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
     * Delete site
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $siteId = $request->getAttribute('id');

        /** @var SiteEntity $site */
        $site = $this->siteManager->fetchSiteByUuid($siteId);
        if (null === $site) {
            throw SiteNotFoundException::whenSearchingByUuid($siteId);
        }

        // check if we are deleting the site
        if ($confirm = $request->getAttribute('confirm')) {
            if (strcmp($confirm, 'confirm') === 0) {
                $this->siteManager->deleteSite($site);

                /** @var FlashMessagesInterface $flashMessage */
                $flashMessage = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);
                $flashMessage->flash('info', 'Site deleted');

                return new RedirectResponse($this->urlHelper->generate('organisation_site.list', [
                    'id' => $site->getOrganisation()->getUuid(),
                ]));

            }
        }

        return new HtmlResponse($this->renderer->render('site::delete-site', [
            'site' => $site,
        ]));
    }
}