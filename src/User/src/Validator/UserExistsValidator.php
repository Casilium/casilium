<?php

declare(strict_types=1);

namespace User\Validator;

use Laminas\Validator\AbstractValidator;
use User\Entity\User;

use function is_array;
use function is_scalar;

/**
 * This validator class is designed for checking if there is an existing user
 * with such an email.
 */
class UserExistsValidator extends AbstractValidator
{
    /**
     * Available validator options.
     *
     * @var array
     */
    protected $options = [
        'entityManager' => null,
        'user'          => null,
    ];

    // Validation failure message IDs.
    public const NOT_SCALAR  = 'notScalar';
    public const USER_EXISTS = 'userExists';

    /**
     * Validation failure messages.
     *
     * @var array
     */
    protected array $messageTemplates = [
        self::NOT_SCALAR  => "The email must be a scalar value",
        self::USER_EXISTS => "Another user with such an email already exists",
    ];

    /**
     * @param array|null $options
     */
    public function __construct(?array $options = null)
    {
        // Set filter options (if provided).
        if (is_array($options)) {
            if (isset($options['entityManager'])) {
                $this->options['entityManager'] = $options['entityManager'];
            }
            if (isset($options['user'])) {
                $this->options['user'] = $options['user'];
            }
        }

        // Call the parent class constructor
        parent::__construct($options);
    }

    /**
     * Check if user exists.
     *
     * @param mixed $value
     */
    public function isValid($value): bool
    {
        if (! is_scalar($value)) {
            $this->error(self::NOT_SCALAR);
            return false;
        }

        // Get Doctrine entity manager.
        $entityManager = $this->options['entityManager'];

        $user = $entityManager->getRepository(User::class)
            ->findOneByEmail($value);

        if ($this->options['user'] === null) {
            $isValid = $user === null;
        } else {
            if ($this->options['user']->getEmail() !== $value && $user !== null) {
                $isValid = false;
            } else {
                $isValid = true;
            }
        }

        // If there were an error, set error message.
        if (! $isValid) {
            $this->error(self::USER_EXISTS);
        }

        // Return validation result.
        return $isValid;
    }
}
