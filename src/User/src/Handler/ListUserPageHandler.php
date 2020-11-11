<?php
declare(strict_types=1);

namespace User\Handler;

use Doctrine\ORM\EntityManagerInterface;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use User\Entity\User;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;

class ListUserPageHandler implements RequestHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TemplateRendererInterface
     */
    private $renderer;

    public function __construct(EntityManagerInterface $entityManager, TemplateRendererInterface $renderer)
    {
        $this->entityManager = $entityManager;
        $this->renderer = $renderer;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $users = $this->entityManager->getRepository(User::class)
            ->findBy([], ['email' => 'ASC']);

        // TODO Paginate

        return new HtmlResponse($this->renderer->render('user::list', ['users' => $users]));
    }
}
