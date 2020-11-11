<?php
declare(strict_types=1);

namespace User\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use User\Service\AuthManager;
use User\Service\RbacManager;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Authentication\UserInterface;
use Mezzio\Helper\UrlHelper;
use Mezzio\Router\RouterInterface;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Template\TemplateRendererInterface;

class AuthorisationMiddleware implements MiddlewareInterface
{
    /**
     * @var AuthManager
     */
    private $authManager;

    /**
     * @var RbacManager
     */
    private $rbac;

    /**
     * @var TemplateRendererInterface
     */
    private $renderer;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    public function __construct(
        RouterInterface $router,
        UrlHelper $helper,
        RbacManager $rbac,
        AuthManager $authManager,
        TemplateRendererInterface $renderer
    ) {
        $this->router = $router;
        $this->rbac = $rbac;
        $this->urlHelper = $helper;
        $this->authManager = $authManager;
        $this->renderer = $renderer;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $routeResult = $this->router->match($request);
        $matchedRouteName = $routeResult->getMatchedRouteName();
        if ($matchedRouteName == null) {
            return $handler->handle($request);
        }

        /** @var SessionInterface $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        $identity = $session->has(UserInterface::class) ? $session->get(UserInterface::class) : null;
        $username = $identity['username'] ?? null;

        $result = $this->authManager->filterAccess($matchedRouteName, $username);
        if ($result == $this->authManager::AUTH_REQUIRED) {
            return new RedirectResponse($this->urlHelper->generate('login'));
        } elseif ($result == $this->authManager::ACCESS_DENIED) {
           // die('x');
            return new HtmlResponse($this->renderer->render('error::403', ['layout' => 'layout::clean']), 403);
        }

        $this->renderer->addDefaultParam($this->renderer::TEMPLATE_ALL, 'identity', $identity['details']['id']);
        return $handler->handle($request);
    }
}
