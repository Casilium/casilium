<?php

declare(strict_types=1);

namespace OrganisationSite\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use OrganisationSite\Entity\SiteEntity;
use OrganisationSite\Exception\SiteNotFoundException;
use OrganisationSite\Form\SiteForm;
use OrganisationSite\Hydrator\SiteEntityHydrator;
use OrganisationSite\Service\SiteManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class EditSiteHandler implements RequestHandlerInterface
{
    /** @var SiteManager */
    protected $siteManager;

    /** @var TemplateRendererInterface */
    protected $renderer;

    /** @var UrlHelper */
    protected $urlHelper;

    /** @var array */
    protected $countries = [];

    public function __construct(SiteManager $siteManager, TemplateRendererInterface $renderer, UrlHelper $urlHelper)
    {
        $this->siteManager = $siteManager;
        $this->renderer    = $renderer;
        $this->urlHelper   = $urlHelper;
    }

    /**
     * Handles edit site requests
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // get side identifier (as uuid) from url variable
        $siteUuid = $request->getAttribute('id');

        /** @var SiteEntity $site */
        $site = $this->siteManager->fetchSiteByUuid($siteUuid);
        if (null === $site) {
            throw SiteNotFoundException::whenSearchingByUuid($siteUuid);
        }

        // grab the list of available countries
        if (empty($this->countries)) {
            $this->countries = $this->siteManager->getCountries();
        }

        // create new site form, set hydrator and bind to SiteEntity object
        $form     = new SiteForm($this->countries);
        $hydrator = new SiteEntityHydrator($this->siteManager);
        $form->setHydrator($hydrator);
        $form->bind($site);

        if ($request->getMethod() === 'POST') {
            // populate form with post data
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                /** @var SiteEntity $object */
                $object = $form->getData();

                // update the site
                $this->siteManager->updateSite($object);

                $flash = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);
                $flash->flash('info', 'Site updated successfully');
                return new RedirectResponse($this->urlHelper->generate('organisation_site.read', [
                    'id' => $site->getUuid(),
                ]));
            }
        }

        return new HtmlResponse($this->renderer->render('site::edit-site', [
            'form' => $form,
            'site' => $site,
        ]));
    }
}
