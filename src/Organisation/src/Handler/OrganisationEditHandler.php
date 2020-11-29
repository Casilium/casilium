<?php

declare(strict_types=1);

namespace Organisation\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Form\FormInterface;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Organisation\Exception\OrganisationNotFoundException;
use Organisation\Form\OrganisationForm;
use Organisation\Hydrator\OrganisationHydrator;
use Organisation\Service\OrganisationManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class OrganisationEditHandler implements RequestHandlerInterface
{
    /** @var OrganisationManager */
    protected $organisationManager;

    /** @var TemplateRendererInterface */
    protected $renderer;

    /** @var UrlHelper */
    protected $urlHelper;

    public function __construct(
        OrganisationManager $organisationManager,
        TemplateRendererInterface $renderer,
        UrlHelper $urlHelper
    ) {
        $this->organisationManager = $organisationManager;
        $this->renderer            = $renderer;
        $this->urlHelper           = $urlHelper;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // get id from url (must be valid uuid)
        $id = $request->getAttribute('id');

        // find organisation
        $organisation = $this->organisationManager->findOrganisationByUuid($id);
        if (null === $organisation) {
            throw OrganisationNotFoundException::whenSearchingByUuid($id);
        }

        // bind organisation to form
        $form = new OrganisationForm('edit');
        $form->setHydrator(new OrganisationHydrator());
        $form->bind($organisation);

        if ('POST' === $request->getMethod()) {
            $form->setData($request->getParsedBody());
            if ($form->isValid()) {
                $this->organisationManager->updateOrganisation(
                    $organisation->getId(),
                    $form->getData(FormInterface::VALUES_AS_ARRAY)
                );

                // redirect to organisation view
                return new RedirectResponse($this->urlHelper->generate('organisation.view', [
                    'id' => $organisation->getUuid(),
                ]));
            }
        }

        return new HtmlResponse($this->renderer->render('organisation::edit', [
            'form' => $form,
            'id'   => $organisation->getUuid(),
        ]));
    }
}
