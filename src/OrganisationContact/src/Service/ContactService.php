<?php

declare(strict_types=1);

namespace OrganisationContact\Service;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Organisation\Entity\Organisation;
use Organisation\Service\OrganisationManager;
use OrganisationContact\Entity\Contact;
use Ticket\Entity\Ticket;
use Ticket\Entity\TicketResponse;

class ContactService
{
    protected EntityManagerInterface $entityManager;

    protected OrganisationManager $organisationService;

    public function __construct(EntityManagerInterface $entityManager, OrganisationManager $organisationService)
    {
        $this->entityManager       = $entityManager;
        $this->organisationService = $organisationService;
    }

    /**
     * Find organisation by UUID
     */
    public function getOrganisationByUuid(string $uuid): ?Organisation
    {
        return $this->organisationService->findOrganisationByUuid($uuid);
    }

    public function createContact(Contact $contact): Contact
    {
        // if contact has an id then it already exists
        if ($contact->getId() > 0) {
            return $contact;
        }

        $organisation = $contact->getOrganisation();
        if ($organisation === null) {
            throw new InvalidArgumentException('Contact must have an organisation set');
        }
        $contact->setOrganisation($organisation);

        // save entity
        $this->entityManager->persist($contact);
        $this->entityManager->flush();

        return $contact;
    }

    /**
     * Fetch contacts for an organisation
     */
    public function fetchContactsByOrganisationId(int $id, bool $activeOnly = false): ?array
    {
        $repo     = $this->entityManager->getRepository(Contact::class);
        $criteria = ['organisation' => $id];
        if ($activeOnly) {
            $criteria['isActive'] = true;
        }
        return $repo->findBy($criteria, ['firstName' => 'ASC', 'lastName' => 'ASC']);
    }

    /**
     * Get contact by id
     */
    public function findContactById(int $id): ?Contact
    {
        return $this->entityManager->getRepository(Contact::class)
            ->find($id);
    }

    /**
     * Update contact details
     */
    public function updateContact(Contact $updatedContact): void
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

    public function findContactByWorkEmail(string $email): ?Contact
    {
        return $this->entityManager->getRepository(Contact::class)->findOneBy(['workEmail' => $email]);
    }

    public function findContactByOtherEmail(string $email): ?Contact
    {
        return $this->entityManager->getRepository(Contact::class)->findOneBy(['otherEmail' => $email]);
    }

    public function deleteContact(Contact $contact): void
    {
        $this->entityManager->remove($contact);
        $this->entityManager->flush();
    }

    public function deactivateContact(Contact $contact): void
    {
        $contact->setIsActive(false);
        $this->entityManager->flush();
    }

    public function canDeleteContact(Contact $contact): bool
    {
        $ticketCount = (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(t.id)')
            ->from(Ticket::class, 't')
            ->where('t.contact = :contact')
            ->setParameter('contact', $contact->getId())
            ->getQuery()
            ->getSingleScalarResult();

        if ($ticketCount > 0) {
            return false;
        }

        $responseCount = (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(r.id)')
            ->from(TicketResponse::class, 'r')
            ->where('r.contact = :contact')
            ->setParameter('contact', $contact->getId())
            ->getQuery()
            ->getSingleScalarResult();

        return $responseCount === 0;
    }
}
