<?php
declare(strict_types=1);

namespace User\Middleware;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Helper\UrlHelper;
use Mezzio\Router\RouterInterface;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use User\Service\AuthManager;
use User\Service\RbacManager;
use User\Service\UserManager;
use UserAuthentication\Entity\IdentityInterface;

class AuthorisationMiddleware implements MiddlewareInterface
{
    /** @var UserManager */
    protected $userManager;

    /** @var AuthManager */
    protected $authManager;

    /** @var RbacManager */
    protected $rbac;

    /** @var TemplateRendererInterface */
    protected $renderer;

    /** @var RouterInterface */
    protected $router;

    /** @var UrlHelper */
    protected $urlHelper;

    public function __construct(
        UserManager $userManager,
        RouterInterface $router,
        UrlHelper $helper,
        RbacManager $rbac,
        AuthManager $authManager,
        TemplateRendererInterface $renderer
    ) {
        $this->userManager = $userManager;
        $this->router      = $router;
        $this->rbac        = $rbac;
        $this->urlHelper   = $helper;
        $this->authManager = $authManager;
        $this->renderer    = $renderer;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $routeResult      = $this->router->match($request);
        $matchedRouteName = $routeResult->getMatchedRouteName();
        if ($matchedRouteName === false) {
            return $handler->handle($request);
        }

        $session  = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        $identity = $session->has(IdentityInterface::class) ? $session->get(IdentityInterface::class) : null;
        if ($identity !== null) {
            $identity = $this->authManager->createIdentityFromArray($identity);
            $request  = $request->withAttribute(IdentityInterface::class, $identity);
        }

        // check access
        $result = $this->authManager->filterAccess($matchedRouteName, $identity === null ? null : $identity->getId());
        if ($result === AuthManager::AUTH_REQUIRED) {
            // if requires auth, redirect to login
            return new RedirectResponse($this->urlHelper->generate('login'));
        } elseif ($result === AuthManager::ACCESS_DENIED) {
            // if authed and denied access, show 403
            return new HtmlResponse($this->renderer->render('error::403', ['layout' => 'layout::clean']), 403);
        }

        $this->renderer->addDefaultParam(
            $this->renderer::TEMPLATE_ALL,
            'identity',
            $identity === null ? null : $identity->getId()
        );
        return $handler->handle($request);
    }
}
