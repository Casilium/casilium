<?php

declare(strict_types=1);

namespace UserAuthentication\Handler;

use App\Traits\CsrfTrait;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Authentication\UserInterface;
use Mezzio\Csrf\CsrfMiddleware;
use Mezzio\Helper\UrlHelper;
use Mezzio\Session\Session;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use UserAuthentication\Form\LoginForm;

class LoginPageHandler implements MiddlewareInterface
{
    use CsrfTrait;

    /**
     * @var $session
     */
    protected $session;

    /**
     * @var TemplateRendererInterface
     */
    protected $renderer;

    /**
     * @var UrlHelper
     */
    protected $helper;

    /** @var StorageInterface */
    protected $cache;

    public function __construct(TemplateRendererInterface $renderer, UrlHelper $helper, $cache)
    {
        $this->renderer = $renderer;
        $this->helper = $helper;
        $this->cache = $cache;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Session $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        if ($session->has(UserInterface::class)) {
            return new RedirectResponse('/');
        }

        $this->session = $session;
        return $this->handleLogin($request, $handler);
    }

    public function handleLogin(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var SessionInterface $session */
        $session = $this->session;

        $guard = $request->getAttribute(CsrfMiddleware::GUARD_ATTRIBUTE);
        $token = $this->getToken($session, $guard);

        $form = new LoginForm($guard);
        $error = null;
        if ('POST' === $request->getMethod()) {
            $form->setData($request->getParsedBody());
            if ($form->isValid()) {
                $response = $handler->handle($request);

                if ($response->getStatusCode() !== 302) {
                    $user = $session->get(UserInterface::class);
                    if (null === $user) {
                        throw new \Exception('User not found during login?');
                    }

                    $status = (int) $user['details']['status'] ?? 0;
                    $user_id = (int) $user['details']['id'] ?? null;
                    $mfa_enabled = (int) $user['details']['mfa_enabled'] ?? 0;

                    if ($status === 1) {
                        if ($mfa_enabled  > 0) {
                            $this->cache->addItem('auth:cached_user:' . $user_id, $user);
                            $session->unset(UserInterface::class);
                            $session->set('mfa:user:id', $user_id);
                            return new RedirectResponse($this->helper->generate('mfa.validate'));
                        }

                        return new RedirectResponse($this->helper->generate('home'));
                    }

                    $session->clear();
                    $error = 'User is not inactive';
                }  else {
                    $error = 'Invalid username and/or password.';
                }

            }
            // regenerate csrf on failure
            $token = $this->getToken($session, $guard);
        }

        $form->get('csrf')->setValue($token);
        return new HtmlResponse($this->renderer->render('user_auth::login-page', [
            'form' => $form,
            'error' => $error,
            'layout'     => 'layout::clean',
        ]));
    }
}