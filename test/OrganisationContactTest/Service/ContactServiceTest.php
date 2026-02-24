<?php

declare(strict_types=1);

namespace OrganisationContactTest\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Organisation\Entity\Organisation;
use Organisation\Service\OrganisationManager;
use OrganisationContact\Entity\Contact;
use OrganisationContact\Repository\ContactRepository;
use OrganisationContact\Service\ContactService;
use OrganisationSite\Entity\SiteEntity;
use Override;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class ContactServiceTest extends TestCase
{
    use ProphecyTrait;

    /** @psalm-suppress PropertyNotSetInConstructor */
    private ?ContactService $contactService;

    /** @psalm-suppress PropertyNotSetInConstructor */
    private ?ObjectProphecy $entityManager;

    /** @psalm-suppress PropertyNotSetInConstructor */
    private ?ObjectProphecy $organisationService;

    #[Override]
    protected function setUp(): void
    {
        /** @var ObjectProphecy $entityManager */
        $entityManager = $this->prophesize(EntityManagerInterface::class);

        /** @var ObjectProphecy $organisationService */
        $organisationService = $this->prophesize(OrganisationManager::class);

        $this->entityManager       = $entityManager;
        $this->organisationService = $organisationService;

        $this->contactService = new ContactService(
            $this->entityManager->reveal(),
            $organisationService->reveal()
        );
    }

    public function testGetOrganisationByUuid(): void
    {
        $uuid         = 'test-org-uuid';
        $organisation = $this->createMock(Organisation::class);

        $this->organisationService->findOrganisationByUuid($uuid)
            ->willReturn($organisation);

        $result = $this->contactService->getOrganisationByUuid($uuid);

        $this->assertSame($organisation, $result);
    }

    public function testGetOrganisationByUuidReturnsNull(): void
    {
        $uuid = 'non-existent-uuid';

        $this->organisationService->findOrganisationByUuid($uuid)
            ->willReturn(null);

        $result = $this->contactService->getOrganisationByUuid($uuid);

        $this->assertNull($result);
    }

    public function testCreateContactWithNewContact(): void
    {
        $organisation = $this->createMock(Organisation::class);
        $organisation->method('getId')->willReturn(123);

        $contact = $this->createMock(Contact::class);
        $contact->method('getId')->willReturn(0); // New contact
        $contact->method('getOrganisation')->willReturn($organisation);
        $contact->expects($this->once())->method('setOrganisation')->with($organisation);

        $this->entityManager->persist($contact)->shouldBeCalled();
        $this->entityManager->flush()->shouldBeCalled();

        $result = $this->contactService->createContact($contact);

        $this->assertSame($contact, $result);
    }

    public function testCreateContactWithExistingContact(): void
    {
        $contact = $this->createMock(Contact::class);
        $contact->method('getId')->willReturn(456); // Existing contact

        // Should not persist or flush for existing contact
        $this->entityManager->persist(Argument::any())->shouldNotBeCalled();
        $this->entityManager->flush()->shouldNotBeCalled();

        $result = $this->contactService->createContact($contact);

        $this->assertSame($contact, $result);
    }

    public function testFetchContactsByOrganisationId(): void
    {
        $organisationId = 789;
        $contacts       = [
            $this->createMock(Contact::class),
            $this->createMock(Contact::class),
        ];

        $repository = $this->prophesize(ContactRepository::class);

        $this->entityManager->getRepository(Contact::class)
            ->willReturn($repository->reveal());
        $repository->findBy(
            ['organisation' => $organisationId],
            ['firstName' => 'ASC', 'lastName' => 'ASC']
        )->willReturn($contacts);

        $result = $this->contactService->fetchContactsByOrganisationId($organisationId);

        $this->assertSame($contacts, $result);
    }

    public function testFetchContactsByOrganisationIdReturnsNull(): void
    {
        $organisationId = 999;
        $contacts       = [];

        $repository = $this->prophesize(ContactRepository::class);

        $this->entityManager->getRepository(Contact::class)
            ->willReturn($repository->reveal());
        $repository->findBy(
            ['organisation' => $organisationId],
            ['firstName' => 'ASC', 'lastName' => 'ASC']
        )->willReturn($contacts);

        $result = $this->contactService->fetchContactsByOrganisationId($organisationId);

        $this->assertSame($contacts, $result);
    }

    public function testFetchContactsByOrganisationIdActiveOnly(): void
    {
        $organisationId = 789;
        $contacts       = [
            $this->createMock(Contact::class),
        ];

        $repository = $this->prophesize(ContactRepository::class);

        $this->entityManager->getRepository(Contact::class)
            ->willReturn($repository->reveal());
        $repository->findBy(
            ['organisation' => $organisationId, 'isActive' => true],
            ['firstName' => 'ASC', 'lastName' => 'ASC']
        )->willReturn($contacts);

        $result = $this->contactService->fetchContactsByOrganisationId($organisationId, true);

        $this->assertSame($contacts, $result);
    }

    public function testFindContactById(): void
    {
        $contactId = 101;
        $contact   = $this->createMock(Contact::class);

        $repository = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(Contact::class)
            ->willReturn($repository->reveal());
        $repository->find($contactId)->willReturn($contact);

        $result = $this->contactService->findContactById($contactId);

        $this->assertSame($contact, $result);
    }

    public function testFindContactByIdReturnsNull(): void
    {
        $contactId = 404;

        $repository = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(Contact::class)
            ->willReturn($repository->reveal());
        $repository->find($contactId)->willReturn(null);

        $result = $this->contactService->findContactById($contactId);

        $this->assertNull($result);
    }

    public function testUpdateContact(): void
    {
        $contactId = 202;
        $site      = $this->createMock(SiteEntity::class);

        // Current contact in database
        $currentContact = $this->createMock(Contact::class);

        // Updated contact data
        $updatedContact = $this->createMock(Contact::class);
        $updatedContact->method('getId')->willReturn($contactId);
        $updatedContact->method('getSite')->willReturn($site);
        $updatedContact->method('getFirstName')->willReturn('John');
        $updatedContact->method('getMiddleName')->willReturn('David');
        $updatedContact->method('getLastName')->willReturn('Smith');
        $updatedContact->method('getWorkTelephone')->willReturn('+44 20 1234 5678');
        $updatedContact->method('getWorkExtension')->willReturn('1234');
        $updatedContact->method('getMobileTelephone')->willReturn('+44 7700 123456');
        $updatedContact->method('getHomeTelephone')->willReturn('+44 20 8765 4321');
        $updatedContact->method('getWorkEmail')->willReturn('john@company.com');

        $repository = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(Contact::class)
            ->willReturn($repository->reveal());
        $repository->find($contactId)->willReturn($currentContact);

        // Verify all setter methods are called on current contact
        $currentContact->expects($this->once())->method('setSite')->with($site)->willReturn($currentContact);
        $currentContact->expects($this->once())->method('setFirstName')->with('John')->willReturn($currentContact);
        $currentContact->expects($this->once())->method('setMiddleName')->with('David')->willReturn($currentContact);
        $currentContact->expects($this->once())->method('setLastName')->with('Smith')->willReturn($currentContact);
        $currentContact->expects($this->once())
            ->method('setWorkTelephone')
            ->with('+44 20 1234 5678')
            ->willReturn($currentContact);
        $currentContact->expects($this->once())->method('setWorkExtension')->with('1234')->willReturn($currentContact);
        $currentContact->expects($this->once())
            ->method('setMobileTelephone')
            ->with('+44 7700 123456')
            ->willReturn($currentContact);
        $currentContact->expects($this->once())
            ->method('setHomeTelephone')
            ->with('+44 20 8765 4321')
            ->willReturn($currentContact);
        $currentContact->expects($this->once())
            ->method('setWorkEmail')
            ->with('john@company.com')
            ->willReturn($currentContact);

        $this->entityManager->flush()->shouldBeCalled();

        $this->contactService->updateContact($updatedContact);
    }

    public function testDeleteContact(): void
    {
        $contact = $this->createMock(Contact::class);

        $this->entityManager->remove($contact)->shouldBeCalled();
        $this->entityManager->flush()->shouldBeCalled();

        $this->contactService->deleteContact($contact);
    }

    public function testUpdateContactDoesNotSetGender(): void
    {
        $contactId = 303;

        $currentContact = $this->createMock(Contact::class);
        $updatedContact = $this->createMock(Contact::class);
        $updatedContact->method('getId')->willReturn($contactId);
        $updatedContact->method('getSite')->willReturn(null);
        $updatedContact->method('getFirstName')->willReturn('Jane');
        $updatedContact->method('getMiddleName')->willReturn('');
        $updatedContact->method('getLastName')->willReturn('Doe');
        $updatedContact->method('getWorkTelephone')->willReturn('');
        $updatedContact->method('getWorkExtension')->willReturn('');
        $updatedContact->method('getMobileTelephone')->willReturn('');
        $updatedContact->method('getHomeTelephone')->willReturn('');
        $updatedContact->method('getWorkEmail')->willReturn('jane@test.com');

        $repository = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(Contact::class)
            ->willReturn($repository->reveal());
        $repository->find($contactId)->willReturn($currentContact);

        // Gender setter should NOT be called (commented out in service)
        $currentContact->expects($this->never())->method('setGender');

        // Other setters should still be called
        $currentContact->expects($this->once())->method('setFirstName')->with('Jane')->willReturn($currentContact);
        $currentContact->expects($this->once())->method('setLastName')->with('Doe')->willReturn($currentContact);
        $currentContact->expects($this->once())
            ->method('setWorkEmail')
            ->with('jane@test.com')
            ->willReturn($currentContact);
        $currentContact->expects($this->once())->method('setSite')->with(null)->willReturn($currentContact);
        $currentContact->expects($this->once())->method('setMiddleName')->with('')->willReturn($currentContact);
        $currentContact->expects($this->once())->method('setWorkTelephone')->with('')->willReturn($currentContact);
        $currentContact->expects($this->once())->method('setWorkExtension')->with('')->willReturn($currentContact);
        $currentContact->expects($this->once())->method('setMobileTelephone')->with('')->willReturn($currentContact);
        $currentContact->expects($this->once())->method('setHomeTelephone')->with('')->willReturn($currentContact);

        $this->entityManager->flush()->shouldBeCalled();

        $this->contactService->updateContact($updatedContact);
    }

    public function testUpdateContactDoesNotSetOtherEmail(): void
    {
        $contactId = 404;

        $currentContact = $this->createMock(Contact::class);
        $updatedContact = $this->createMock(Contact::class);
        $updatedContact->method('getId')->willReturn($contactId);
        $updatedContact->method('getSite')->willReturn(null);
        $updatedContact->method('getFirstName')->willReturn('Test');
        $updatedContact->method('getMiddleName')->willReturn('');
        $updatedContact->method('getLastName')->willReturn('User');
        $updatedContact->method('getWorkTelephone')->willReturn('');
        $updatedContact->method('getWorkExtension')->willReturn('');
        $updatedContact->method('getMobileTelephone')->willReturn('');
        $updatedContact->method('getHomeTelephone')->willReturn('');
        $updatedContact->method('getWorkEmail')->willReturn('test@work.com');

        $repository = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(Contact::class)
            ->willReturn($repository->reveal());
        $repository->find($contactId)->willReturn($currentContact);

        // Other email setter should NOT be called (missing from service)
        $currentContact->expects($this->never())->method('setOtherEmail');

        // Work email should be set
        $currentContact->expects($this->once())
            ->method('setWorkEmail')
            ->with('test@work.com')
            ->willReturn($currentContact);

        // All other standard setters should be called
        $currentContact->expects($this->once())->method('setSite')->willReturn($currentContact);
        $currentContact->expects($this->once())->method('setFirstName')->willReturn($currentContact);
        $currentContact->expects($this->once())->method('setMiddleName')->willReturn($currentContact);
        $currentContact->expects($this->once())->method('setLastName')->willReturn($currentContact);
        $currentContact->expects($this->once())->method('setWorkTelephone')->willReturn($currentContact);
        $currentContact->expects($this->once())->method('setWorkExtension')->willReturn($currentContact);
        $currentContact->expects($this->once())->method('setMobileTelephone')->willReturn($currentContact);
        $currentContact->expects($this->once())->method('setHomeTelephone')->willReturn($currentContact);

        $this->entityManager->flush()->shouldBeCalled();

        $this->contactService->updateContact($updatedContact);
    }
}
