<?php

declare(strict_types=1);

namespace Account\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Authentication\UserInterface;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AccountPageHandler implements RequestHandlerInterface
{
    /** @var TemplateRendererInterface */
    private $renderer;

    public function __construct(TemplateRendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session     = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        $user        = $session->get(UserInterface::class);
        $mfa_enabled = (int) $user['details']['mfa_enabled'];

        return new HtmlResponse($this->renderer->render('account::account-page', [
            'mfa_enabled' => $mfa_enabled,
        ]));
    }
}
