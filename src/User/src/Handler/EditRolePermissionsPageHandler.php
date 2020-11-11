<?php
declare(strict_types=1);

namespace User\Handler;

use App\Traits\CsrfTrait;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use User\Entity\Permission;
use User\Entity\Role;
use User\Form\RolePermissionsForm;
use User\Service\RoleManager;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Csrf\CsrfMiddleware;
use Mezzio\Csrf\SessionCsrfGuard;
use Mezzio\Helper\UrlHelper;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Template\TemplateRendererInterface;

class EditRolePermissionsPageHandler implements RequestHandlerInterface
{
    use CsrfTrait;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UrlHelper
     */
    private $helper;

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
        TemplateRendererInterface $renderer,
        UrlHelper $helper
    ) {
        $this->entityManager = $entityManager;
        $this->roleManager = $roleManager;
        $this->renderer = $renderer;
        $this->helper = $helper;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int)$request->getAttribute('id', -1);
        if ($id < 1) {
            return new HtmlResponse($this->renderer->render('error::404'), 404);
        }

        /** @var Role $role */
        $role = $this->entityManager->getRepository(Role::class)->find($id);
        if ($role == null) {
            return new HtmlResponse($this->renderer->render('error::404'), 404);
        }

        $allPermissions = $this->entityManager->getRepository(Permission::class)
            ->findBy([], ['name' => 'ASC']);

        $effectivePermissions = $this->roleManager->getEffectivePermissions($role);

        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        /** @var SessionCsrfGuard $guard */
        $guard = $request->getAttribute(CsrfMiddleware::GUARD_ATTRIBUTE);
        $token = $this->getToken($session, $guard);


        // create form
        $form = new RolePermissionsForm($this->entityManager, $guard);
        /** @var Permission $permission */
        foreach ($allPermissions as $permission) {
            $label = $permission->getName();
            $isDisabled = false;
            if (isset($effectivePermissions[$permission->getName()])
                && $effectivePermissions[$permission->getName()] == 'inherited') {
                $label .= ' (inherited)';
                $isDisabled = true;
            }
            $form->addPermissionField($permission->getName(), $label, $isDisabled);
        }

        if ($request->getMethod() === 'POST') {
            $form->setData($request->getParsedBody());
            if ($form->isValid()) {
                $data = $form->getData();
                if (! is_array($data)) {
                    throw new \Exception(sprintf('Expected array return type, got %s', gettype($data)));
                }

                $this->roleManager->updateRolePermissions($role, $data);
                return new RedirectResponse($this->helper->generate('admin.role-view', [
                    'id' => $role->getId()
                ]));
            }

            $token = $this->getToken($session, $guard);
        } else {
            $data = [];
            foreach ($effectivePermissions as $name => $inherited) {
                $data['permissions'][$name] = 1;
            }

            $form->setData($data);
        }

        $errors = $form->getMessages();
        return new HtmlResponse($this->renderer->render('role::edit-permissions', [
            'form' => $form,
            'role' => $role,
            'allPermissions' => $allPermissions,
            'effectivePermissions' => $effectivePermissions,
            'errors' => $errors,
            'token' => $token,
        ]));
    }
}
