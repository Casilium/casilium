<?php

declare(strict_types=1);

namespace Ticket\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ticket\Service\CannedResponseManager;

class ListCannedResponseHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly CannedResponseManager $cannedResponseManager,
        private readonly TemplateRendererInterface $renderer
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new HtmlResponse($this->renderer->render('ticket::list-canned-response', [
            'cannedResponses' => $this->cannedResponseManager->findAll(),
        ]));
    }
}
