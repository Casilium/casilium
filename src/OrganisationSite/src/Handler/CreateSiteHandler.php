<?php

declare(strict_types=1);

namespace OrganisationSite\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Flash\FlashMessagesInterface;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Organisation\Entity\Organisation;
use OrganisationSite\Entity\SiteEntity;
use OrganisationSite\Form\SiteForm;
use OrganisationSite\Hydrator\SiteEntityHydrator;
use OrganisationSite\Service\SiteManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CreateSiteHandler implements RequestHandlerInterface
{
    /** @var SiteManager */
    protected $siteManager;

    /** @var TemplateRendererInterface */
    protected $renderer;

    /** @var UrlHelper */
    protected $urlHelper;

    /** @var array */
    protected $countries = [];

    /** @var Organisation */
    protected $organisation;

    public function __construct(SiteManager $siteManager, TemplateRendererInterface $renderer, UrlHelper $urlHelper)
    {
        $this->siteManager = $siteManager;
        $this->renderer    = $renderer;
        $this->urlHelper   = $urlHelper;
    }

    /**
     * Handle request to create new site
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var FlashMessagesInterface $flashMessenger */
        $flashMessenger = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);

        // grab the list of available countries
        if (empty($this->countries)) {
            $this->countries = $this->siteManager->getCountries();
        }

        // fetch the organisation relating to the site
        $organisationUuid = $request->getAttribute('id');

        // save organisation locally
        if (null === $this->organisation) {
            $this->organisation = $this->siteManager->getOrganisationByUuid($organisationUuid);
        }

        // create site form (passing in list of countries)
        $form = new SiteForm($this->countries);
        $form->setHydrator(new SiteEntityHydrator($this->siteManager));
        $form->bind(new SiteEntity());

        // set default country to UK if not set
        if ($form->get('country_id')->getValue() === null) {
            $form->get('country_id')->setValue(230);
        }

        // form has been posted?
        if ($request->getMethod() === 'POST') {
            // populate form data
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                /** @var SiteEntity $site */
                $site = $form->getData();

                // set site custodian
                $site->setOrganisation($this->organisation);
                $this->siteManager->createSite($site);

                $flashMessenger->flash('info', 'Site created successfully');
                return new RedirectResponse($this->urlHelper->generate('organisation.list'));
            }
        }

        return new HtmlResponse($this->renderer->render('site::create-site', [
            'form'         => $form,
            'organisation' => $this->organisation,
        ]));
    }
}
