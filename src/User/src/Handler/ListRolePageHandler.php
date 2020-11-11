<?php
declare(strict_types=1);

namespace User\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use User\Entity\Role;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;

class ListRolePageHandler implements RequestHandlerInterface
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TemplateRendererInterface
     */
    private $renderer;

    public function __construct(
        EntityManagerInterface $entityManager,
        TemplateRendererInterface $renderer
    ) {

        $this->entityManager = $entityManager;
        $this->renderer = $renderer;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $roles = $this->entityManager->getRepository(Role::class)
            ->findBy([], ['name' => 'ASC']);

        return new HtmlResponse($this->renderer->render('role::list', ['roles' => $roles]));
    }
}
