<?php
declare(strict_types=1);

namespace UserAuthentication\View\Helper;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Laminas\View\Helper\AbstractHelper;
use User\Entity\User;
use UserAuthentication\Entity\Identity;

class IdentityViewHelper extends AbstractHelper
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var Identity */
    private $user;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Return this class so methods are accessible from view
     *
     * @throws Exception
     */
    public function __invoke(): ?Identity
    {
        if ($this->view->identity === null) {
            return null;
        }

        if ($this->user == null) {
            $identity = $this->view->identity;

            /** @var User $user */
            $user = $this->entityManager->getRepository(User::class)->find($identity);

            $identity = new Identity();
            $identity->setId($user->getId());
            $identity->setEmail($user->getEmail());
            $identity->setName($user->getFullName());
            $identity->setRoles($user->getRolesAsString());
            $this->user = $identity;
        }

        return $this->user;
    }
}
