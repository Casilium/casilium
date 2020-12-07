<?php

declare(strict_types=1);

namespace Account\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Mfa\Service\MfaService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use UserAuthentication\Entity\IdentityInterface;

class AccountPageHandler implements RequestHandlerInterface
{
    /** @var TemplateRendererInterface */
    private $renderer;

    /** @var MfaService */
    private $mfaService;

    public function __construct(TemplateRendererInterface $renderer, MfaService $mfaService)
    {
        $this->renderer   = $renderer;
        $this->mfaService = $mfaService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user       = $request->getAttribute(IdentityInterface::class);
        $mfaEnabled = $this->mfaService->hasMfa($user);

        return new HtmlResponse($this->renderer->render('account::account-page', [
            'mfa_enabled' => $mfaEnabled,
        ]));
    }
}
