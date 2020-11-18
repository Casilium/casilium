<?php
declare(strict_types=1);

namespace User\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\EventManager\EventManagerInterface;
use Mezzio\Authentication\UserInterface;
use Mezzio\Session\Session;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use User\Entity\User;

class ViewUserPageHandler implements RequestHandlerInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var TemplateRendererInterface */
    private $renderer;

    /** @var EventManagerInterface */
    private $events;

    public function __construct(EntityManagerInterface $entityManager, TemplateRendererInterface $renderer, $events)
    {
        $this->entityManager = $entityManager;
        $this->renderer      = $renderer;
        $this->events        = $events;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var Session $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        // get current user to pass to log
        $currentUser = $session->get(UserInterface::class);

        $id = (int) $request->getAttribute('id', -1);
        if ($id < 1) {
            return new HtmlResponse($this->renderer->render('error::404'));
        }

        $user = $this->entityManager->getRepository(User::class)->find($id);
        if ($user == null) {
            return new HtmlResponse($this->renderer->render('error::404'));
        }

        $this->events->trigger('user.view', $this, [
            'user_id'      => $currentUser['details']['id'],
            'view_user_id' => $user->getId(),
        ]);

        return new HtmlResponse($this->renderer->render('user::view', [
            'user' => $user,
        ]));
    }
}
