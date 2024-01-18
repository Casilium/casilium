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
use User\Form\UserForm;
use User\Service\UserManager;

use function gettype;
use function is_array;
use function sprintf;

class AddUserPageHandler implements RequestHandlerInterface
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
        /** @var Session $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        /** @var SessionCsrfGuard $guard */
        $guard = $request->getAttribute(CsrfMiddleware::GUARD_ATTRIBUTE);
        $token = $this->getToken($session, $guard);

        $form = new UserForm($guard, 'create', $this->entityManager);

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

                $user = $this->userManager->addUser($data);
                return new RedirectResponse($this->urlHelper->generate('admin.user.list'));
            }

            $token = $this->getToken($session, $guard);
        }

        return new HtmlResponse($this->renderer->render('user::add', [
            'form'  => $form,
            'token' => $token,
        ]));
    }
}
