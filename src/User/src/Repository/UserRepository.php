<?php
declare(strict_types=1);

namespace User\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query;
use User\Entity\User;

class UserRepository extends EntityRepository
{
    public function findAllUsers(): Query
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('u')
            ->from(User::class, 'u')
            ->orderBy('u.dateCreated', 'DESC');

        return $queryBuilder->getQuery();
    }

    /**
     * @return object|null
     */
    public function findOneByEmail(string $email)
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(User $user): void
    {
        if ($user->getId() == null) {
            $this->_em->persist($user);
        }

        $this->_em->flush();
    }

    /**
     * @param false $fetchCredentials
     */
    public function findUserById(int $id, $fetchCredentials = false): ?User
    {
        /** @var User $user */
        $user = $this->find($id);
        if (null === $user) {
            return null;
        }

        if (false === $fetchCredentials) {
            $user->setPassword('hidden');
            $user->setSecretKey('hidden');
            $user->setPasswordResetToken('hidden');
        }

        return $user;
    }
}
