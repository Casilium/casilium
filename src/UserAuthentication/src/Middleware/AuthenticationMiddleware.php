<?php

declare(strict_types=1);

namespace UserAuthentication\Middleware;

use Mezzio\Session\SessionMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use UserAuthentication\Entity\IdentityInterface;
use UserAuthentication\Service\AuthenticationService;

class AuthenticationMiddleware implements MiddlewareInterface
{
    /** @var AuthenticationService */
    private $authenticationService;

    public function __construct(AuthenticationService $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            $params = $request->getParsedBody();
            if (isset($params['username']) && isset($params['password'])) {
                $identity = $this->authenticationService->authenticate($params['username'], $params['password']);
                if ($identity instanceof IdentityInterface) {
                    return $handler->handle($request->withAttribute(IdentityInterface::class, $identity));
                }
            }
        }

        return $handler->handle($request)->withStatus(302);
    }
}
