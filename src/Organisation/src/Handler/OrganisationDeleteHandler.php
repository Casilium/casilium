<?php

declare(strict_types=1);

namespace Organisation\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Organisation\Exception\OrganisationSitesExistException;
use Organisation\Service\OrganisationManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class OrganisationDeleteHandler implements RequestHandlerInterface
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
        UrlHelper $helper
    ) {
        $this->organisationManager = $organisationManager;
        $this->renderer            = $renderer;
        $this->urlHelper           = $helper;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // get organisation uuid from url and find organisation
        $id           = $request->getAttribute('id');
        $organisation = $this->organisationManager->findOrganisationByUuid($id);

        if ($request->getMethod() === 'POST') {
            // delete on post
            try {
                $this->organisationManager->delete($organisation);
            } catch (OrganisationSitesExistException $exception) {
                $flashMessages = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);
                $flashMessages->flash('error', $exception->getMessage());
            }

            return new RedirectResponse($this->urlHelper->generate('organisation.list'));
        }

        return new HtmlResponse($this->renderer->render('organisation::delete', [
            'organisation' => $organisation,
        ]));
    }
}
