<?php

declare(strict_types=1);

namespace Organisation\Handler;

use Organisation\Exception\OrganisationNotFoundException;
use Organisation\Form\OrganisationForm;
use Organisation\Service\OrganisationManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;

class OrganisationEditHandler implements RequestHandlerInterface
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
    ) {
        $this->organisationManager = $organisationManager;
        $this->renderer = $renderer;
        $this->urlHelper = $urlHelper;
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
        $form->bind($organisation);

        if ('POST' === $request->getMethod()) {
            // set form data
            $form->setData($request->getParsedBody());
            if ($form->isValid()) {
                // get filtered results
                $data = $form->getData();

                // update the organisation
                $organisation = $this->organisationManager->updateOrganisation($organisation, $data->getArrayCopy());

                // redirect to organisation view
                return new RedirectResponse($this->urlHelper->generate('organisation.view', [
                    'id' => $organisation->getUuid(),
                ]));
            }
        }

        return new HtmlResponse($this->renderer->render('organisation::edit', [
            'form' => $form,
            'id' => $organisation->getUuid(),
        ]));
    }
}