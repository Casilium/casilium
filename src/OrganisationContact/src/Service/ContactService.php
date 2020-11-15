<?php

declare(strict_types=1);

namespace OrganisationContact\Service;

use Doctrine\ORM\EntityManagerInterface;
use Organisation\Entity\Organisation;
use Organisation\Service\OrganisationManager;
use OrganisationContact\Entity\Contact;

class ContactService
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var OrganisationManager
     */
    protected $organisationService;

    public function __construct(EntityManagerInterface $entityManager, OrganisationManager $organisationService)
    {
        $this->entityManager = $entityManager;
        $this->organisationService = $organisationService;
    }

    /**
     * Find organisation by UUID
     *
     * @param string $uuid
     * @return Organisation|null
     */
    public function getOrganisationByUuid(string $uuid): ?Organisation
    {
        return $this->organisationService->findOrganisationByUuid($uuid);
    }

    /**
     * @param Contact $contact
     *
     * @return Contact|null
     */
    public function createContact(Contact $contact): ?Contact
    {
        // if contact has an id then it already exists
        if ($contact->getId() > 0) {
            return $contact;
        }

        // save entity
        $this->entityManager->persist($contact);
        $this->entityManager->flush();

        return $contact;
    }

    /**
     * Fetch contacts for an organisation
     *
     * @param int $id
     * @return array
     */
    public function fetchContactsByOrganisationId(int $id): ?array
    {
        return $this->entityManager->getRepository(Contact::class)
            ->findByCorporationId($id);
    }

    /**
     * Get contact by id
     *
     * @param int $id
     * @return null|Contact
     */
    public function findContactById(int $id): ?Contact
    {
        return $this->entityManager->getRepository(Contact::class)
            ->find($id);
    }

    /**
     * Update contact details
     *
     * @param Contact $updatedContact
     */
    public function updateContact(Contact $updatedContact)
    {
        /** @var Contact $currentContactContact */
        $currentContactContact = $this->entityManager->getRepository(Contact::class)
            ->find($updatedContact->getId());

        $currentContactContact->setSite($updatedContact->getSite());
        $currentContactContact->setFirstName($updatedContact->getFirstName());
        $currentContactContact->setMiddleName($updatedContact->getMiddleName());
        $currentContactContact->setLastName($updatedContact->getLastName());
        $currentContactContact->setWorkTelephone($updatedContact->getWorkTelephone());
        $currentContactContact->setWorkExtension($updatedContact->getWorkExtension());
        $currentContactContact->setMobileTelephone($updatedContact->getMobileTelephone());
        $currentContactContact->setHomeTelephone($updatedContact->getHomeTelephone());
        $currentContactContact->setWorkEmail($updatedContact->getWorkEmail());
        //$currentContactContact->setGender($updatedContact->getGender());

        $this->entityManager->flush();
    }
}
