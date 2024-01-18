<?php

declare(strict_types=1);

namespace User\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use User\Entity\Role;
use User\Service\RoleManager;

use function array_key_exists;

class DeleteRolePageHandler implements RequestHandlerInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var UrlHelper */
    private $helper;

    /** @var TemplateRendererInterface */
    private $renderer;

    /** @var RoleManager */
    private $roleManager;

    public function __construct(
        RoleManager $roleManager,
        EntityManagerInterface $entityManager,
        TemplateRendererInterface $renderer,
        UrlHelper $urlHelper
    ) {
        $this->roleManager   = $roleManager;
        $this->entityManager = $entityManager;
        $this->renderer      = $renderer;
        $this->helper        = $urlHelper;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int) $request->getAttribute('id', -1);
        if ($id < 1) {
            return new HtmlResponse($this->renderer->render('error::404'), 404);
        }

        /** @var Role $role */
        $role = $this->entityManager->getRepository(Role::class)
            ->find($id);

        if ($role === null) {
            return new HtmlResponse($this->renderer->render('error::404'), 404);
        }

        $params = $request->getQueryParams();
        if (array_key_exists('confirm', $params) && (bool) $params['confirm'] === true) {
            if ($role->getId() === 1) {
                throw new Exception('The Administrator role cannot be deleted!');
            }

            $this->roleManager->deleteRole($role);
            return new RedirectResponse($this->helper->generate('admin.role.list'));
        }

        return new HtmlResponse($this->renderer->render('role::delete-role', ['role' => $role]));
    }
}
