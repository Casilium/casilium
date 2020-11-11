<?php
declare(strict_types=1);

namespace UserAuthentication\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Authentication\UserInterface;
use Mezzio\Helper\UrlHelper;
use Mezzio\Session\SessionMiddleware;

class LogoutPageHandler implements RequestHandlerInterface
{
    /**
     * @var UrlHelper
     */
    private $helper;

    public function __construct(UrlHelper $helper)
    {
        $this->helper = $helper;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        if ($session->has(UserInterface::class)) {
            $session->clear();
        }

        return new RedirectResponse($this->helper->generate('login'));
    }
}
