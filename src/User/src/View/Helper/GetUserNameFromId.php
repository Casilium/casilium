<?php

declare(strict_types=1);

namespace User\View\Helper;

use Auth\Entity\Identity;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Laminas\View\Helper\AbstractHelper;
use User\Entity\User;

use function array_key_exists;
use function is_object;

class GetUserNameFromId extends AbstractHelper
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var array */
    private $users = [];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Return this class so methods are accessible from view
     *
     * @var int $id
     * @return Identity
     * @throws Exception
     */
    public function __invoke(int $id): ?string
    {
        if (array_key_exists($id, $this->users)) {
            return $this->users[$id];
        }

        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (is_object($user)) {
            $this->users[$id] = $user->getFullName();
            return $this->users[$id];
        }

        return null;
    }
}
