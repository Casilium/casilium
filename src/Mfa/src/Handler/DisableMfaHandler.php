<?php

declare(strict_types=1);

namespace Mfa\Handler;

use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Helper\UrlHelper;
use Mfa\Service\MfaService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use UserAuthentication\Entity\IdentityInterface;

class DisableMfaHandler implements RequestHandlerInterface
{
    /** @var MfaService */
    private $mfa;

    /** @var UrlHelper */
    private $urlHelper;

    public function __construct(MfaService $mfa, UrlHelper $urlHelper)
    {
        $this->mfa       = $mfa;
        $this->urlHelper = $urlHelper;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute(IdentityInterface::class);
        $this->mfa->disableMfa($user);

        return new RedirectResponse($this->urlHelper->generate('account'));
    }
}
