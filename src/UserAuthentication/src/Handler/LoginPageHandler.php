<?php

declare(strict_types=1);

namespace UserAuthentication\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Authentication\UserInterface;
use Mezzio\Csrf\CsrfMiddleware;
use Mezzio\Csrf\SessionCsrfGuard;
use Mezzio\Helper\UrlHelper;
use Mezzio\Session\Session;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Template\TemplateRendererInterface;
use Mfa\Service\MfaService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use UserAuthentication\Entity\IdentityInterface;
use UserAuthentication\Form\LoginForm;
use UserAuthentication\Service\AuthenticationService;

class LoginPageHandler implements MiddlewareInterface
{
    /** @var AuthenticationService */
    protected $authService;

    /** @var MfaService */
    protected $mfa;

    /** @var TemplateRendererInterface */
    protected $renderer;

    /** @var UrlHelper */
    protected $helper;

    /** @var SessionInterface */
    protected $session;

    public function __construct(
        AuthenticationService $authService,
        MfaService $mfa,
        TemplateRendererInterface $renderer,
        UrlHelper $helper
    ) {
        $this->authService = $authService;
        $this->mfa         = $mfa;
        $this->renderer    = $renderer;
        $this->helper      = $helper;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Session $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        if ($session->has(IdentityInterface::class) && ! empty($session->get(IdentityInterface::class))) {
            return new RedirectResponse('/');
        }

        $this->session = $session;
        return $this->handleLogin($request, $handler);
    }

    public function handleLogin(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var SessionCsrfGuard $guard */
        $guard = $request->getAttribute(CsrfMiddleware::GUARD_ATTRIBUTE);
        $form  = new LoginForm($guard);

        if ('POST' === $request->getMethod()) {
            $form->setData($request->getParsedBody());
            if ($form->isValid()) {
                $data = $form->getData();
                if ($identity = $this->authService->authenticate($data['username'], $data['password'])) {
                    $request = $request->withAttribute(UserInterface::class, $identity);
                    return $handler->handle($request);
                }
            }
            return new RedirectResponse($this->helper->generate('login'));
        }

        $token = $guard->generateToken();
        $form->get('csrf')->setValue($token);
        return new HtmlResponse($this->renderer->render('user_auth::login-page', [
            'form'   => $form,
            'layout' => 'layout::clean',
        ]));
    }
}
