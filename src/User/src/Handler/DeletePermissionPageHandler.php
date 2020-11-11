<?php
declare(strict_types=1);

namespace User\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use User\Entity\Permission;
use User\Service\PermissionManager;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;

class DeletePermissionPageHandler implements RequestHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UrlHelper
     */
    private $helper;

    /**
     * @var PermissionManager
     */
    private $permissionManager;

    /**
     * @var TemplateRendererInterface
     */
    private $renderer;

    /**
     * DeletePermissionPageHandler constructor.
     * @param EntityManagerInterface $entityManager
     * @param PermissionManager $permissionManager
     * @param TemplateRendererInterface $renderer
     * @param UrlHelper $helper
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        PermissionManager $permissionManager,
        TemplateRendererInterface $renderer,
        UrlHelper $helper
    ) {
        $this->entityManager = $entityManager;
        $this->renderer = $renderer;
        $this->permissionManager = $permissionManager;
        $this->helper = $helper;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
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

        $this->permissionManager->deletePermission($permission);
        return new RedirectResponse($this->helper->generate('admin.permission.list'));
    }
}
