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

class ViewPermissionPageHandler implements RequestHandlerInterface
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
        $id = (int)$request->getAttribute('id', -1);
        if ($id < 1) {
            return new HtmlResponse($this->renderer->render('error::404'), 404);
        }

        /** @var Permission $permission */
        $permission = $this->entityManager->getRepository(Permission::class)->find($id);
        if ($permission == null) {
            return new HtmlResponse($this->renderer->render('error::404'), 404);
        }

        return new HtmlResponse($this->renderer->render('permission::view', [
            'permission' => $permission,
        ]));
    }
}
