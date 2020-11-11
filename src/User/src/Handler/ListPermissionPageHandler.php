<?php
declare(strict_types=1);

namespace User\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use User\Entity\Permission;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;

class ListPermissionPageHandler implements RequestHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TemplateRendererInterface
     */
    private $renderer;

    /**
     * ListPermissionPageHandler constructor.
     * @param EntityManagerInterface $entityManager
     * @param TemplateRendererInterface $renderer
     */
    public function __construct(EntityManagerInterface $entityManager, TemplateRendererInterface $renderer)
    {
        $this->entityManager = $entityManager;
        $this->renderer = $renderer;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $permissions = $this->entityManager->getRepository(Permission::class)
            ->findBy([], ['name' => 'ASC']);

        return new HtmlResponse($this->renderer->render('permission::list', [
            'permissions' => $permissions,
        ]));
    }
}
