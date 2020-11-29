<?php
declare(strict_types=1);

namespace User\Middleware;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Authentication\UserInterface;
use Mezzio\Helper\UrlHelper;
use Mezzio\Router\RouterInterface;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use User\Service\AuthManager;
use User\Service\RbacManager;

class AuthorisationMiddleware implements MiddlewareInterface
{
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
        RouterInterface $router,
        UrlHelper $helper,
        RbacManager $rbac,
        AuthManager $authManager,
        TemplateRendererInterface $renderer
    ) {
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
        if ($matchedRouteName === null) {
            return $handler->handle($request);
        }

        /** @var SessionInterface $session */
        $session  = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        $identity = $session->has(UserInterface::class) ? $session->get(UserInterface::class) : null;
        $username = $identity['username'] ?? null;

        $result = $this->authManager->filterAccess($matchedRouteName, $username);
        if ($result === AuthManager::AUTH_REQUIRED) {
            return new RedirectResponse($this->urlHelper->generate('login'));
        } elseif ($result === AuthManager::ACCESS_DENIED) {
            return new HtmlResponse($this->renderer->render('error::403', ['layout' => 'layout::clean']), 403);
        }

        $this->renderer->addDefaultParam($this->renderer::TEMPLATE_ALL, 'identity', $identity['details']['id']);
        return $handler->handle($request);
    }
}
