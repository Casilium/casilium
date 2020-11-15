<?php

declare(strict_types=1);

namespace OrganisationContact\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use OrganisationContact\Exception\ContactNotFoundException;
use OrganisationContact\Service\ContactService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DeleteContactHandler implements RequestHandlerInterface
{
    /**
     * @var ContactService
     */
    protected $contactService;

    /**
     * @var TemplateRendererInterface
     */
    protected $renderer;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    /**
     * ViewContactHandler constructor.
     *
     * @param ContactService $service
     * @param TemplateRendererInterface $renderer
     * @param UrlHelper $helper
     */
    public function __construct(
        ContactService $service,
        TemplateRendererInterface $renderer,
        UrlHelper $helper
    ) {
        $this->contactService = $service;
        $this->renderer = $renderer;
        $this->urlHelper = $helper;
    }

    /**
     * Render contact html page
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $contactId = (int)$request->getAttribute('contact_id');
        $contact = $this->contactService->findContactById($contactId);
        if ($contact === 0) {
            throw ContactNotFoundException::whenSearchingById($contactId);
        }

        $confirm = (bool) $request->getAttribute('confirm');
        if (true === $confirm) {
            $this->contactService->deleteContact($contact);
            $flashMessages = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);
            $flashMessages->flash('info', 'Contact has been deleted');
            return new RedirectResponse($this->urlHelper->generate('contact.list', [
                'id' => $contact->getOrganisation()->getUuid(),
            ]));
        }

        return new HtmlResponse($this->renderer->render('contact::delete', ['contact' => $contact]));
    }
}