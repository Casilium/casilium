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
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

use function is_array;
use function sprintf;

final class CreateContactHandler implements RequestHandlerInterface
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

    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** var flashMessage **/
        $flashMessages = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);

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
            $parsedBody = $request->getParsedBody();
            if (! is_array($parsedBody)) {
                throw new RuntimeException('Unexpected form data');
            }
            $form->setData($parsedBody);
            if ($form->isValid()) {
                $formData = $form->getInputFilter()->getValues();

                // check work_email is not used anywhere
                if (
                    ! empty($formData['work_email'])
                    && (
                        $this->contactService->findContactByWorkEmail($formData['work_email']) !== null
                        || $this->contactService->findContactByOtherEmail($formData['work_email']) !== null
                    )
                ) {
                    $form->get('work_email')
                        ->setMessages(['A contact with this email already exists']);

                    return new HtmlResponse(
                        $this->renderer->render('contact::create', [
                            'form'         => $form,
                            'organisation' => $organisation,
                        ])
                    );
                }

                // check other_email is not used anywhere
                if (
                    ! empty($formData['other_email'])
                    && (
                        $this->contactService->findContactByOtherEmail($formData['other_email']) !== null
                        || $this->contactService->findContactByWorkEmail($formData['other_email']) !== null
                    )
                ) {
                    $form->get('other_email')
                        ->setMessages(['A contact with this email already exists']);

                    return new HtmlResponse(
                        $this->renderer->render('contact::create', [
                            'form'         => $form,
                            'organisation' => $organisation,
                        ])
                    );
                }

                $contact = new Contact();
                $contact->exchangeArray($formData);
                $contact->setIsActive(true);

                $contact->setOrganisation($organisation);
                $result = $this->contactService->createContact($contact);
                if (! $result instanceof Contact) {
                    $flashMessages->flash('error', 'An error ocurred creating contact');

                    return new HtmlResponse(
                        $this->renderer->render('contact::create', [
                            'form'         => $form,
                            'organisation' => $organisation,
                        ])
                    );
                }

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
