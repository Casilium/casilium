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
    protected ContactService $contactService;

    protected TemplateRendererInterface $renderer;

    protected UrlHelper $urlHelper;

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
    //    $form->bind(new Contact());

        if ($request->getMethod() === 'POST') {
            // set form data from POST vars
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                $contact = new Contact();
                $contact->exchangeArray($form->getInputFilter()->getValues());

                $contact->setOrganisation($organisation);
                $result = $this->contactService->createContact($contact);

                $flashMessages = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);
                $flashMessages->flash('info', sprintf(
                    'Contact "%s %s" created',
                    $contact->getFirstName(),
                    $contact->getLastName()
                ));

                return new RedirectResponse($this->urlHelper->generate('contact.list', [
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
