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

class AddRolePageHandler implements RequestHandlerInterface
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
        /** @var Session $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        /** @var SessionCsrfGuard $guard */
        $guard = $request->getAttribute(CsrfMiddleware::GUARD_ATTRIBUTE);
        $token = $this->getToken($session, $guard);

        $form = new RoleForm($guard, $this->entityManager);

        $roleList = [];
        $roles    = $this->entityManager->getRepository(Role::class)
            ->findBy([], ['name' => 'ASC']);

        /** @var Role $role */
        foreach ($roles as $role) {
            $roleList[$role->getId()] = $role->getName();
        }

        $form->get('inherit_roles')->setValueOptions($roleList);

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            $form->setData($data);

            if ($form->isValid()) {
                $data = $form->getData();
                if (! is_array($data)) {
                    throw new Exception(sprintf('Expected array return type, got %s', gettype($data)));
                }

                $this->roleManager->addRole($data);

                return new RedirectResponse($this->helper->generate('admin.role.list'));
            }

            $token = $this->getToken($session, $guard);
        }

        return new HtmlResponse($this->renderer->render('role::add', [
            'form'  => $form,
            'token' => $token,
        ]));
    }
}
