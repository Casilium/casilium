<?php

declare(strict_types=1);

namespace App\Doctrine;

use App\Doctrine\UuidEncoder;

/**
 * Trait RepositoryUuidFinderTrait
 *
 * A repository trait to easily find an entity by encoded UUID
 */
trait RepositoryUuidFinderTrait
{
    /** @var UuidEncoder */
    protected $uuidEncoder;

    public function findOneByEncodedUuid(string $encodedUuid)
    {
        return $this->findOneBy([
            'uuid' => $this->uuidEncoder->decode($encodedUuid),
        ]);
    }
}
