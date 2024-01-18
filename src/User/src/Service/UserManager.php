<?php

declare(strict_types=1);

namespace User\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Laminas\Crypt\Password\Bcrypt;
use Mezzio\Template\TemplateRendererInterface;
use User\Entity\Role;
use User\Entity\User;
use User\Exception\PasswordMismatchException;

use function bin2hex;
use function date;
use function password_hash;
use function password_verify;
use function random_bytes;
use function sprintf;
use function strcmp;
use function strtotime;
use function time;

use const PASSWORD_BCRYPT;

class UserManager
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var RoleManager */
    protected $roleManager;

    /** @var PermissionManager */
    protected $permissionManager;

    /** @var TemplateRendererInterface */
    protected $renderer;

    /** @var array */
    protected $config;

    public function __construct(
        EntityManagerInterface $entityManager,
        RoleManager $roleManager,
        PermissionManager $permissionManager,
        TemplateRendererInterface $renderer,
        array $config
    ) {
        $this->entityManager     = $entityManager;
        $this->roleManager       = $roleManager;
        $this->permissionManager = $permissionManager;
        $this->renderer          = $renderer;
        $this->config            = $config;
    }

    public function addUser(array $data): ?User
    {
        if ($this->checkUserExists($data['email'])) {
            throw new Exception(sprintf("User with email address %s already exists", $data['email']));
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setFullName($data['full_name']);
        $passwordHash = $this->getPasswordHash($data['password']);
        $user->setPassword($passwordHash);
        $user->setStatus($data['status']);
        $user->setDateCreated(date('Y-m-d H:i:s'));

        $this->assignRoles($user, $data['roles']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function getPasswordHash(string $password): string
    {
        $bcrypt = new Bcrypt();
        return $bcrypt->create($password);
    }

    public function updateUser(User $user, array $data): bool
    {
        // Do not allow to change user email if another user with such email already exits.
        if ($user->getEmail() !== $data['email'] && $this->checkUserExists($data['email'])) {
            throw new Exception("Another user with email address " . $data['email'] . " already exists");
        }

        $user->setEmail($data['email']);
        $user->setFullName($data['full_name']);
        $user->setStatus($data['status']);
        $user->setMfaEnabled((bool) $data['mfa_enabled']);

        // Assign roles to user.
        $this->assignRoles($user, $data['roles']);

        // Apply changes to database.
        $this->entityManager->flush();
        return true;
    }

    public function assignRoles(User $user, array $roleIds): void
    {
        //remove old user role(s).
        $user->getRoles()->clear();

        // assign new role(s).
        foreach ($roleIds as $roleId) {
            /** @var Role $role */
            $role = $this->entityManager->getRepository(Role::class)->find($roleId);

            if ($role === null) {
                throw new Exception('Not found role by ID');
            }

            $user->addRole($role);
        }
    }

    public function checkUserExists(string $email): bool
    {
        $user = $this->entityManager->getRepository(User::class)->findOneByEmail($email);
        return $user !== null;
    }

    public function generatePasswordResetToken(User $user): void
    {
        if ($user->getStatus() !== User::STATUS_ACTIVE) {
            throw new Exception('Cannot generate password reset token for inactive user');
        }

        // generate token
        $selector = bin2hex(random_bytes(8));
        $token    = bin2hex(random_bytes(32));

        // encrypt token before storing it in db
        $tokenHash = password_hash($token, PASSWORD_BCRYPT);

        // save token to DB
        $user->setPasswordResetToken($tokenHash);

        // save token creation date to DB
        $currentDate = date('Y-m-d H:i:s');
        $user->setPasswordResetTokenCreationDate($currentDate);

        $this->entityManager->flush();
    }

    public function verifyPasswordResetToken(string $email, string $passwordResetToken): bool
    {
        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class);
        if (null === $user || $user->getStatus() !== User::STATUS_ACTIVE) {
            return false;
        }

        // check token has matches the token in our DB
        if (password_verify($passwordResetToken, $user->getPasswordResetToken())) {
            return false; // mismatch
        }

        $tokenCreationDate = $user->getPasswordResetTokenCreationDate();
        $tokenCreationDate = strtotime($tokenCreationDate);

        $currentDate = time();

        return ! ($currentDate - $tokenCreationDate > 24 * 60 * 60);
    }

    public function changePassword(int $id, string $currentPassword, string $newPassword): bool
    {
        if (strcmp($currentPassword, $newPassword) === 0) {
            throw PasswordMismatchException::whenPasswordsAreSame();
        }

        $user = $this->entityManager->getRepository(User::class)
            ->find($id);

        if ($user instanceof User) {
            // verify password
            if (! password_verify($currentPassword, $user->getPassword())) {
                throw PasswordMismatchException::whenVerifying();
            }

            // set new password
            $user->setPassword(password_hash($newPassword, PASSWORD_BCRYPT));

            // save password
            $this->entityManager->flush();
        }

        return false;
    }

    public function findById(int $id): User
    {
        return $this->entityManager->getRepository(User::class)->find($id);
    }
}
