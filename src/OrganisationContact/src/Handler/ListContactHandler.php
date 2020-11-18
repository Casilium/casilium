<?php

declare(strict_types=1);

namespace OrganisationContact\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Organisation\Entity\Organisation;
use Organisation\Exception\OrganisationNotFoundException;
use OrganisationContact\Service\ContactService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ListContactHandler implements RequestHandlerInterface
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

        $contacts = $this->contactService->fetchContactsByOrganisationId($organisation->getId());

        return new HtmlResponse($this->renderer->render('contact::list', [
            'contacts'     => $contacts,
            'organisation' => $organisation,
        ]));
    }
}
