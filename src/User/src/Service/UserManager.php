<?php
declare(strict_types=1);

namespace User\Service;

use Doctrine\ORM\EntityManagerInterface;
use User\Entity\Role;
use User\Entity\User;
use Laminas\Crypt\Password\Bcrypt;
use Mezzio\Template\TemplateRendererInterface;
use User\Exception\PasswordMismatchException;

class UserManager
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var RoleManager
     */
    protected $roleManager;

    /**
     * @var PermissionManager
     */
    protected $permissionManager;

    /**
     * @var TemplateRendererInterface
     */
    protected $renderer;

    /**
     * @var array
     */
    protected $config;

    /**
     * UserManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param RoleManager $roleManager
     * @param PermissionManager $permissionManager
     * @param TemplateRendererInterface $renderer
     * @param array $config
     */
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
     *
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
        $password_hash = password_hash($data['password'], PASSWORD_BCRYPT);
        $user->setPassword($password_hash);
        $user->setStatus($data['status']);
        $user->setDateCreated(date('Y-m-d H:i:s'));

        $this->assignRoles($user, $data['roles']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * Create Password Hash
     *
     * @param string $password
     * @return string
     */
    public function getPasswordHash(string $password): string
    {
        $bcrypt = new Bcrypt();
        return $bcrypt->create($password);
    }

    /**
     * Update an existing user
     *
     * @param User $user
     * @param array $data
     * @return bool
     * @throws \Exception
     */
    public function updateUser(User $user, array $data): bool
    {
        // Do not allow to change user email if another user with such email already exits.
        if ($user->getEmail() !== $data['email'] && $this->checkUserExists($data['email'])) {
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
     *
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

            if ($role === null) {
                throw new \Exception('Not found role by ID');
            }

            $user->addRole($role);
        }
    }

    /**
     * Checks whether a user with the given email address already exists in the database
     *
     * @param string $email
     * @return bool
     */
    public function checkUserExists(string $email): bool
    {
        $user = $this->entityManager->getRepository(User::class)
            ->findOneByEmail($email);

        return $user !== null;
    }

    /**
     * Generate password reset token to send to user via email
     *
     * @param User $user
     * @throws \Exception
     */
    public function generatePasswordResetToken(User $user): void
    {
        if ($user->getStatus() !== User::STATUS_ACTIVE) {
            throw new \Exception('Cannot generate password reset token for inactive user');
        }

        // generate token
        $selector = bin2hex(random_bytes(8));
        $token = bin2hex(random_bytes(32));

        // encrypt token before storing it in db
        $tokenHash = password_hash($token, PASSWORD_BCRYPT);

        // save token to DB
        $user->setPasswordResetToken($tokenHash);

        // save token creation date to DB
        $currentDate = date('Y-m-d H:i:s');
        $user->setPasswordResetTokenCreationDate($currentDate);

        $this->entityManager->flush();
    }

    /**
     * Verify password reset token
     *
     * @param string $email
     * @param string $passwordResetToken
     * @return bool
     */
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

        return !($currentDate - $tokenCreationDate > 24 * 60 * 60);
    }

    /**
     * Change current user password
     *
     * @param int $id user id
     * @param string $current_password current password
     * @param string $new_password the new password
     * @return bool
     */
    public function changePassword(int $id, string $current_password, string $new_password): bool
    {
        if (strcmp($current_password,$new_password) === 0) {
            throw PasswordMismatchException::whenPasswordsAreSame();
        }

        $user = $this->entityManager->getRepository(User::class)
            ->find($id);

        if ($user instanceof User) {
            // verify password
            if (! password_verify($current_password, $user->getPassword())) {
                throw PasswordMismatchException::whenVerifying();
            }

            // set new password
            $user->setPassword(password_hash($new_password, PASSWORD_BCRYPT));

            // save password
            $this->entityManager->flush();
        }

        return false;
    }
}
