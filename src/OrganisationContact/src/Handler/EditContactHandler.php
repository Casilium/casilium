<?php

declare(strict_types=1);

namespace OrganisationContact\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use OrganisationContact\Exception\ContactNotFoundException;
use OrganisationContact\Form\ContactForm;
use OrganisationContact\Service\ContactService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class EditContactHandler implements RequestHandlerInterface
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

        $contact_id = (int) $request->getAttribute('contact_id');
        $contact    = $this->contactService->findContactById($contact_id);
        if (null == $contact) {
            throw ContactNotFoundException::whenSearchingById($contact_id);
        }

        $form = new ContactForm();
        $form->bind($contact);
        if ($request->getMethod() === 'POST') {
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                $contact = $form->getData();

                $this->contactService->updateContact($contact);
                $flashMessages = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);
                $flashMessages->flash('info', 'Contact updated');

                return new RedirectResponse($this->urlHelper->generate('contact.list', [
                    'id' => $contact->getOrganisation()->getUuid(),
                ]));
            }
        }

        return new HtmlResponse($this->renderer->render('contact::create', [
            'contact' => $contact,
            'form'    => $form,
        ]));
    }
}
