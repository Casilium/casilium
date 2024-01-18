<?php

declare(strict_types=1);

namespace User\Handler;

use App\Traits\CsrfTrait;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Csrf\CsrfMiddleware;
use Mezzio\Csrf\SessionCsrfGuard;
use Mezzio\Helper\UrlHelper;
use Mezzio\Session\Session;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use User\Entity\Role;
use User\Form\RoleForm;
use User\Service\RoleManager;

use function gettype;
use function is_array;
use function sprintf;

class EditRolePageHandler implements RequestHandlerInterface
{
    use CsrfTrait;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var UrlHelper */
    private $helper;

    /** @var RoleManager */
    private $roleManager;

    /** @var TemplateRendererInterface */
    private $renderer;

    public function __construct(
        EntityManagerInterface $entityManager,
        RoleManager $roleManager,
        TemplateRendererInterface $renderer,
        UrlHelper $helper
    ) {
        $this->entityManager = $entityManager;
        $this->roleManager   = $roleManager;
        $this->renderer      = $renderer;
        $this->helper        = $helper;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // get id from route
        $id = (int) $request->getAttribute('id', -1);

        if ($id < 1) {
            return new HtmlResponse($this->renderer->render('error::404'), 404);
        }

        // find role
        /** @var Role $roleToEdit */
        $roleToEdit = $this->entityManager->getRepository(Role::class)->find($id);
        if ($roleToEdit === null) {
            // not found
            return new HtmlResponse($this->renderer->render('error::404'), 404);
        }

        /** @var Session $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        /** @var SessionCsrfGuard $guard */
        $guard = $request->getAttribute(CsrfMiddleware::GUARD_ATTRIBUTE);

        // generate csrf token
        $token = $this->getToken($session, $guard);

        // create form (passing csrf guard)
        $form = new RoleForm($guard, $this->entityManager, $roleToEdit);

        $roleList      = [];
        $selectedRoles = [];

        $roles = $this->entityManager->getRepository(Role::class)
            ->findBy([], ['name' => 'ASC']);

        /** @var Role $role */
        foreach ($roles as $role) {
            if ($role->getId() === $roleToEdit->getId()) {
                continue; // Do not inherit from ourselves
            }

            $roleList[$role->getId()] = $role->getName();

            if ($roleToEdit->hasParent($role)) {
                $selectedRoles[] = $role->getId();
            }
            $form->get('inherit_roles')->setValueOptions($roleList);
            $form->get('inherit_roles')->setValue($selectedRoles);
        }

        if ($request->getMethod() === 'POST') {
            $form->setData($request->getParsedBody());
            if ($form->isValid()) {
                // get filtered and validated data
                $data = $form->getData();
                if (! is_array($data)) {
                    throw new Exception(sprintf('Expected array return type, got %s', gettype($data)));
                }

                // update role
                $this->roleManager->updateRole($roleToEdit, $data);
                return new RedirectResponse($this->helper->generate('admin.role.list'));
            }
            $token = $this->getToken($session, $guard);
        } else {
            $form->setData([
                'name'        => $roleToEdit->getName(),
                'description' => $roleToEdit->getDescription(),
            ]);
        }

        return new HtmlResponse($this->renderer->render('role::edit', [
            'form'  => $form,
            'role'  => $roleToEdit,
            'token' => $token,
        ]));
    }
}
