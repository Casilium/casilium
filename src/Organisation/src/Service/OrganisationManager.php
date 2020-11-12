<?php

declare(strict_types=1);

namespace Organisation\Service;

use Doctrine\ORM\EntityManagerInterface;
use Organisation\Entity\Organisation;
use Organisation\Entity\OrganisationInterface;
use Organisation\Exception\OrganisationExistsException;
use Organisation\Exception\OrganisationNameException;
use Organisation\Exception\OrganisationNotFoundException;
use Organisation\Form\OrganisationForm;
use Ramsey\Uuid\Uuid;

class OrganisationManager
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(EntityManagerInterface  $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Create organisation from object
     *
     * @param OrganisationInterface $organisation
     * @return Organisation|null
     */
    public function createOrganisation(OrganisationInterface $organisation) : ?OrganisationInterface
    {
        // if organisation exists, throw an exception
        if ($result = $this->findOrganisationByName($organisation->getName())) {
            throw OrganisationExistsException::whenCreating($result->getName());
        }

        // save the organisation
        $this->entityManager->persist($organisation);
        $this->entityManager->flush();

        // return the newly created organisationId
        return $organisation;
    }

    /**
     * Create organisation from array
     *
     * @param array $data
     * @return Organisation|null
     * @throws \Exception
     */
    public function createOrganisationFromArray(array $data): ?Organisation
    {
        // create the new organisation and populate it's data with specified array
        $organisation = new Organisation();
        $organisation->setValues($data);

        return $this->createOrganisation($organisation);
    }

    /**
     * Find organisation by name
     *
     * @param string $name
     * @return Organisation|null
     */
    public function findOrganisationByName(string $name) : ?Organisation
    {
        // find organisation in repository
        $organisation = $this->entityManager->getRepository(Organisation::class)
            ->findOneBy(['name' => $name]);

        // return organisation
        if ($organisation instanceof Organisation) {
            return $organisation;
        }

        // or return nothing
        return null;
    }

    /**
     * Find organisation by id
     *
     * @param int $id
     * @return Organisation|null|Object
     */
    public function findOrganisationById(int $id): ?Organisation
    {
        return $this->entityManager->getRepository(Organisation::class)
            ->findOneBy(['id' => $id]);
    }

    /**
     * Find organisation by uuid
     *
     * @param string $uuid
     * @return Organisation|null
     */
    public function findOrganisationByUuid(string $uuid): ?Organisation
    {
        return $this->entityManager->getRepository(Organisation::class)
            ->findOneByUuid($uuid);
    }

    /**
     * Update organisation from array
     *
     * @param OrganisationInterface $target
     * @param array $data
     * @return Organisation
     */
    public function updateOrganisation(OrganisationInterface  $target, array $data): Organisation
    {
        /** @var Organisation $organisation */
        $organisation = $this->entityManager->getRepository(Organisation::class)->find($target->getId());
        if (null === $organisation) {
            throw OrganisationNotFoundException::whenSearchingById($target->getId());
        }

        // if trying to rename, make sure name does not already exists
        if (strcmp($organisation->getName(), $target->getName()) !== 0) {
            $result = $this->findOrganisationByName($target->getName());
            if (null !== $result) {
                throw OrganisationNameException::whenCreating($target->getName());
            }
        }

        $hasChanges = false;
        $name = $data['name'] ?? null;
        $is_active = $data['is_active'] ?? null;

        // update organisation name
        if (null !== $name) {
            $organisation->setName($name);
            $hasChanges = true;
        }

        // update organisation active status
        if (null !== $is_active) {
            $organisation->setIsActive((int)$is_active);
            $hasChanges = true;
        }

        // save changes
        if (true === $hasChanges) {
            $this->entityManager->persist($organisation);
            $this->entityManager->flush();
        }

        return $organisation;
    }

    /**
     * Fetch all organisations
     *
     * @return array
     */
    public function fetchAll(): array
    {
        return $this->entityManager->getRepository(Organisation::class)->findAll();
    }

    /**
     * Delete an organisation
     *
     * @param Organisation $organisation
     */
    public function delete(Organisation $organisation): void
    {
        $this->entityManager->remove($organisation);
        $this->entityManager->flush();
    }
}