<?php
declare(strict_types=1);

namespace User\Service;

use Doctrine\ORM\EntityManagerInterface;
use User\Entity\Role;
use User\Entity\User;
use Laminas\Crypt\Password\Bcrypt;
use Mezzio\Template\TemplateRendererInterface;

class UserManager
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var RoleManager
     */
    private $roleManager;

    /**
     * @var PermissionManager
     */
    private $permissionManager;

    /**
     * @var TemplateRendererInterface
     */
    private $renderer;

    /**
     * @var array
     */
    private $config;

    public function __construct(
        EntityManagerInterface $entityManager,
        RoleManager $roleManager,
        PermissionManager $permissionManager,
        TemplateRendererInterface $renderer,
        array $config
    ) {
        $this->entityManager = $entityManager;
        $this->roleManager = $roleManager;
        $this->permissionManager = $permissionManager;
        $this->renderer = $renderer;
        $this->config = $config;
    }

    /**
     * Add new user to database
     * @param array $data
     * @return User|null
     * @throws \Exception
     */
    public function addUser(array $data): ?User
    {
        if ($this->checkUserExists($data['email'])) {
            throw new \Exception(sprintf("User with email address %s already exists", $data['email']));
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setFullName($data['full_name']);

        $bcrypt = new Bcrypt();
        $password_hash = $bcrypt->create($data['password']);
        $user->setPassword($password_hash);
        $user->setStatus($data['status']);
        $user->setDateCreated(date('Y-m-d H:i:s'));

        $this->assignRoles($user, $data['roles']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * Update an existing user
     * @param User $user
     * @param array $data
     * @return bool
     * @throws \Exception
     */
    public function updateUser(User $user, array $data): bool
    {
        // Do not allow to change user email if another user with such email already exits.
        if ($user->getEmail() != $data['email'] && $this->checkUserExists($data['email'])) {
            throw new \Exception("Another user with email address " . $data['email'] . " already exists");
        }

        $user->setEmail($data['email']);
        $user->setFullName($data['full_name']);
        $user->setStatus($data['status']);
        $user->setMfaEnabled((bool)$data['mfa_enabled']);

        // Assign roles to user.
        $this->assignRoles($user, $data['roles']);

        // Apply changes to database.
        $this->entityManager->flush();
        return true;
    }

    /**
     * A helper method which assigns new roles to the user
     * @param User $user
     * @param array $roleIds
     * @throws \Exception
     */
    public function assignRoles(User $user, array $roleIds): void
    {
        //remove old user role(s).
        $user->getRoles()->clear();

        // assign new role(s).
        foreach ($roleIds as $roleId) {
            /** @var Role $role */
            $role = $this->entityManager->getRepository(Role::class)->find($roleId);

            if ($role == null) {
                throw new \Exception('Not found role by ID');
            }

            $user->addRole($role);
        }
    }

    /**
     * Checks whether a user with the given email address already exists in the database
     * @param string $email
     * @return bool
     */
    public function checkUserExists(string $email): bool
    {
        $user = $this->entityManager->getRepository(User::class)
            ->findOneByEmail($email);

        return $user !== null;
    }

    public function generatePasswordResetToken(User $user): void
    {
        if ($user->getStatus() != User::STATUS_ACTIVE) {
            throw new \Exception('Cannot generate password reset token for inactive user');
        }

        // generate token
        $selector = bin2hex(random_bytes(8));
        $token = bin2hex(random_bytes(32));

        // encrypt token before storing it in db
        $bcrypt = new Bcrypt();
        $tokenHash = $bcrypt->create($token);

        // save token to DB
        $user->setPasswordResetToken($tokenHash);

        // save token creation date to DB
        $currentDate = date('Y-m-d H:i:s');
        $user->setPasswordResetTokenCreationDate($currentDate);

        $this->entityManager->flush();
    }

    public function verifyPasswordResetToken(string $email, string $passwordResetToken)
    {
        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class);
        if (null == $user || $user->getStatus() != User::STATUS_ACTIVE) {
            return false;
        }

        // check token has matches the token in our DB
        $bCrypt = new Bcrypt();
        $tokenHash = $user->getPasswordResetToken();

        if ($bCrypt->verify($passwordResetToken, $tokenHash)) {
            return false; // mismatch
        }

        $tokenCreationDate = $user->getPasswordResetTokenCreationDate();
        $tokenCreationDate = strtotime($tokenCreationDate);

        $currentDate = strtotime('now');

        if ($currentDate - $tokenCreationDate > 24*60*60) {
            return false; // expired
        }

        return true;
    }
}
