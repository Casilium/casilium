<?php

declare(strict_types=1);

namespace Mfa\Middleware;

use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Helper\UrlHelper;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;
use Mfa\Service\MfaService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use UserAuthentication\Entity\IdentityInterface;

class MfaMiddleware implements MiddlewareInterface
{
    /** @var MfaService */
    private $mfaService;

    /** @var UrlHelper */
    private $urlHelper;

    public function __construct(MfaService $mfaService, UrlHelper $urlHelper)
    {
        $this->mfaService = $mfaService;
        $this->urlHelper  = $urlHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var SessionInterface $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        /** @var IdentityInterface $identity */
        $identity = $request->getAttribute(IdentityInterface::class);
        if ($identity instanceof IdentityInterface && $this->mfaService->hasMfa($identity)) {
            $session->set('mfa:user:id', $identity->getId());

            if ($session->has(IdentityInterface::class)) {
                $session->unset(IdentityInterface::class);
            }
            return new RedirectResponse($this->urlHelper->generate('mfa.validate'));
        }

        $session->set(IdentityInterface::class, [
            'id'    => $identity->getId(),
            'email' => $identity->getEmail(),
            'name'  => $identity->getName(),
        ]);

        return new RedirectResponse($this->urlHelper->generate('home'));
    }
}
