<?php

declare(strict_types=1);

namespace Organisation\Handler;

use Organisation\Service\OrganisationManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;

class OrganisationListHandler implements RequestHandlerInterface
{
    /**
     * @var OrganisationManager
     */
    protected $organisationManager;

    /**
     * @var TemplateRendererInterface
     */
    protected $renderer;

    public function __construct(OrganisationManager $organisationManager, TemplateRendererInterface $renderer)
    {
        $this->organisationManager = $organisationManager;
        $this->renderer = $renderer;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $organisations = $this->organisationManager->fetchAll();
        return new HtmlResponse($this->renderer->render('organisation::list', [
            'organisations' => $organisations,
        ]));
    }
}