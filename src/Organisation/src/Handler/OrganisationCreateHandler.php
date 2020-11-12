<?php

declare(strict_types=1);

namespace Organisation\Handler;

use Organisation\Form\OrganisationForm;
use Organisation\Service\OrganisationManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;

class OrganisationCreateHandler implements RequestHandlerInterface
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

    /**
     * OrganisationCreateHandler constructor.
     * @param OrganisationManager $organisationManager
     * @param TemplateRendererInterface $renderer
     * @param UrlHelper $urlHelper
     */
    public function __construct(
        OrganisationManager $organisationManager,
        TemplateRendererInterface $renderer,
        UrlHelper $urlHelper
    )
    {
        $this->organisationManager = $organisationManager;
        $this->renderer = $renderer;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $form = new OrganisationForm();

        $data = $request->getParsedBody();
        $form->setData($data);

        if ('POST' === $request->getMethod()) {
            $form->setData($request->getParsedBody());
            if ($form->isValid()) {

                // get filtered form values
                $data = $form->getData();

                $organisation = $this->organisationManager->createOrganisationFromArray($data);

                if (!$organisation) {
                    return new HtmlResponse($this->renderer->render('organisation::create', ['form' => $form]));
                }
                return new RedirectResponse($this->urlHelper->generate('organisation.view', [
                    'id' => $organisation->getUuid(),
                ]));
            }
        }

        return new HtmlResponse($this->renderer->render('organisation::create', ['form' => $form]));
    }
}