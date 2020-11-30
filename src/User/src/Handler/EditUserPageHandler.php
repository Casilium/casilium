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
use User\Entity\User;
use User\Form\UserForm;
use User\Service\UserManager;
use function gettype;
use function is_array;
use function sprintf;

class EditUserPageHandler implements RequestHandlerInterface
{
    use CsrfTrait;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var UserManager */
    private $userManager;

    /** @var TemplateRendererInterface */
    private $renderer;

    /** @var UrlHelper */
    private $urlHelper;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserManager $userManager,
        TemplateRendererInterface $renderer,
        UrlHelper $urlHelper
    ) {
        $this->entityManager = $entityManager;
        $this->userManager   = $userManager;
        $this->renderer      = $renderer;
        $this->urlHelper     = $urlHelper;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int) $request->getAttribute('id', -1);
        if ($id < 1) {
            return new HtmlResponse($this->renderer->render('error::404'), 404);
        }

        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->find($id);
        if ($user === null) {
            return new HtmlResponse($this->renderer->render('error::404'), 404);
        }

        /** @var Session $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        /** @var SessionCsrfGuard $guard */
        $guard = $request->getAttribute(CsrfMiddleware::GUARD_ATTRIBUTE);
        $token = $this->getToken($session, $guard);

        $form = new UserForm($guard, 'update', $this->entityManager, $user);

        // get the list of all available roles (sorted by name)
        $allRoles = $this->entityManager->getRepository(Role::class)
            ->findBy([], ['name' => 'ASC']);

        $roleList = [];
        /** @var Role $role */
        foreach ($allRoles as $role) {
            $roleList[$role->getId()] = $role->getName();
        }

        $form->get('roles')->setValueOptions($roleList);

        if ($request->getMethod() === 'POST') {
            $form->setData($request->getParsedBody());
            if ($form->isValid()) {
                $data = $form->getData();
                if (! is_array($data)) {
                    throw new Exception(sprintf('Expected array return type, got %s', gettype($data)));
                }

                $this->userManager->updateUser($user, $data);

                // if admin user is being edited, logout
                if ($user->getId() === 1) {
                    return new RedirectResponse($this->urlHelper->generate('logout'));
                }

                return new RedirectResponse($this->urlHelper->generate('admin.user.view', [
                    'id' => $user->getId(),
                ]));
            }
            $token = $this->getToken($session, $guard);
        } else {
            $userRoleIds = [];
            foreach ($user->getRoles() as $role) {
                $userRoleIds[] = $role->getId();
            }

            $form->setData([
                'full_name'   => $user->getFullName(),
                'email'       => $user->getEmail(),
                'status'      => $user->getStatus(),
                'roles'       => $userRoleIds,
                'mfa_enabled' => $user->isMfaEnabled(),
            ]);
        }

        return new HtmlResponse($this->renderer->render('user::edit', [
            'user'  => $user,
            'form'  => $form,
            'token' => $token,
        ]));
    }
}
