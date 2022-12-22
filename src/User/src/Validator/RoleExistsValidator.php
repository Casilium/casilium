<?php

namespace User\Validator;

use Laminas\Validator\AbstractValidator;
use User\Entity\Role;
use function is_array;
use function is_scalar;

/**
 * This validator class is designed for checking if there is an existing role
 * with such a name.
 */
class RoleExistsValidator extends AbstractValidator
{
    /**
     * Available validator options.
     *
     * @var array
     */
    protected $options = [
        'entityManager' => null,
        'role'          => null,
    ];

    // Validation failure message IDs.
    public const NOT_SCALAR  = 'notScalar';
    public const ROLE_EXISTS = 'roleExists';

    /**
     * Validation failure messages.
     *
     * @var array
     */
    protected array $messageTemplates = [
        self::NOT_SCALAR  => "The email must be a scalar value",
        self::ROLE_EXISTS => "Another role with such name already exists",
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
            if (isset($options['role'])) {
                $this->options['role'] = $options['role'];
            }
        }

        // Call the parent class constructor
        parent::__construct($options);
    }

    /**
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

        $role = $entityManager->getRepository(Role::class)
            ->findOneByName($value);

        if ($this->options['role'] === null) {
            $isValid = $role === null;
        } else {
            if ($this->options['role']->getName() !== $value && $role !== null) {
                $isValid = false;
            } else {
                $isValid = true;
            }
        }

        // If there were an error, set error message.
        if (! $isValid) {
            $this->error(self::ROLE_EXISTS);
        }

        // Return validation result.
        return $isValid;
    }
}
