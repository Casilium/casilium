<?php
declare(strict_types=1);

namespace User\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use User\Entity\Permission;
use User\Entity\Role;
use User\Service\RoleManager;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;

class ViewRolePageHandler implements RequestHandlerInterface
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
     * @var RoleManager
     */
    private $roleManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        RoleManager $roleManager,
        TemplateRendererInterface $renderer
    ) {
        $this->entityManager = $entityManager;
        $this->roleManager = $roleManager;
        $this->renderer = $renderer;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int) $request->getAttribute('id', -1);
        if ($id < 1) {
            // role not found
            return new HtmlResponse($this->renderer->render('error::404'), 404);
        }

        // find role with related id
        /** @var Role $role */
        $role = $this->entityManager->getRepository(Role::class)
            ->find($id);

        if ($role == null) {
            // role not found
            return new HtmlResponse($this->renderer->render('error::404'), 404);
        }

        $allPermissions = $this->entityManager->getRepository(Permission::class)
            ->findBy([], ['name' => 'ASC']);

        $effectivePermisions = $this->roleManager->getEffectivePermissions($role);

        return new HtmlResponse($this->renderer->render('role::view', [
            'role' => $role,
            'allPermissions' => $allPermissions,
            'effectivePermissions' => $effectivePermisions,
        ]));
    }
}
