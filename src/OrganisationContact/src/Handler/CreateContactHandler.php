<?php

declare(strict_types=1);

namespace OrganisationContact\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Organisation\Entity\Organisation;
use Organisation\Exception\OrganisationNotFoundException;
use OrganisationContact\Entity\Contact;
use OrganisationContact\Form\ContactForm;
use OrganisationContact\Service\ContactService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function sprintf;

class CreateContactHandler implements RequestHandlerInterface
{
    /** @var ContactService */
    protected $contactService;

    /** @var TemplateRendererInterface */
    protected $renderer;

    /** @var UrlHelper */
    protected $urlHelper;

    public function __construct(
        ContactService $service,
        TemplateRendererInterface $renderer,
        UrlHelper $helper
    ) {
        $this->contactService = $service;
        $this->renderer       = $renderer;
        $this->urlHelper      = $helper;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // get organisation uuid from url
        $orgId = $request->getAttribute('id');

        // find organisation
        $organisation = $this->contactService->getOrganisationByUuid($orgId);
        if (! $organisation instanceof Organisation) {
            throw OrganisationNotFoundException::whenSearchingByUuid($orgId);
        }

        // new contact form and bind to contact object
        $form = new ContactForm();
        $form->bind(new Contact());

        if ($request->getMethod() === 'POST') {
            // set form data from POST vars
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                /** @var Contact $contact */
                $contact = $form->getData();

                $contact->setOrganisation($organisation);
                $result = $this->contactService->createContact($contact);

                $flashMessages = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);
                $flashMessages->flash('info', sprintf(
                    'Contact "%s %s" created',
                    $contact->getFirstName(),
                    $contact->getLastName()
                ));

                return new RedirectResponse($this->urlHelper->generate('organisation.list', [
                    'id' => $contact->getOrganisation()->getUuid(),
                ]));
            }
        }

        return new HtmlResponse($this->renderer->render('contact::create', [
            'form'         => $form,
            'organisation' => $organisation,
        ]));
    }
}
