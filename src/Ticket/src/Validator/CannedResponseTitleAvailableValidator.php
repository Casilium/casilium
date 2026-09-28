<?php

declare(strict_types=1);

namespace Ticket\Validator;

use Doctrine\ORM\EntityManagerInterface;
use Laminas\Validator\AbstractValidator;
use Ticket\Entity\CannedResponse;

use function is_scalar;

class CannedResponseTitleAvailableValidator extends AbstractValidator
{
    public const DUPLICATE_TITLE = 'duplicateTitle';
    public const NOT_SCALAR      = 'notScalar';

    protected array $messageTemplates = [
        self::DUPLICATE_TITLE => 'Another canned response already uses this title',
        self::NOT_SCALAR      => 'The title must be text',
    ];

    private EntityManagerInterface $entityManager;
    private ?CannedResponse $cannedResponse;

    /** @param array{entityManager: EntityManagerInterface, cannedResponse?: CannedResponse|null} $options */
    public function __construct(array $options)
    {
        $this->entityManager  = $options['entityManager'];
        $this->cannedResponse = $options['cannedResponse'] ?? null;

        parent::__construct($options);
    }

    public function isValid(mixed $value): bool
    {
        if (! is_scalar($value)) {
            $this->error(self::NOT_SCALAR);
            return false;
        }

        $existing = $this->entityManager->getRepository(CannedResponse::class)
            ->findOneBy(['title' => (string) $value]);

        if ($existing === null) {
            return true;
        }

        if ($this->cannedResponse !== null && $existing->getId() === $this->cannedResponse->getId()) {
            return true;
        }

        $this->error(self::DUPLICATE_TITLE);
        return false;
    }
}
