<?php

declare(strict_types=1);

namespace TicketTest\Service;

use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Laminas\EventManager\EventManagerInterface;
use MailService\Service\MailService;
use Organisation\Entity\Organisation;
use Organisation\Service\OrganisationManager;
use OrganisationContact\Entity\Contact;
use OrganisationContact\Service\ContactService;
use OrganisationSite\Entity\SiteEntity;
use OrganisationSite\Service\SiteManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Ticket\Entity\Agent;
use Ticket\Entity\Priority;
use Ticket\Entity\Queue;
use Ticket\Entity\Status;
use Ticket\Entity\Ticket;
use Ticket\Entity\Type;
use Ticket\Repository\TicketRepository;
use Ticket\Service\QueueManager;
use Ticket\Service\TicketService;
use User\Entity\User;
use User\Service\UserManager;

class TicketServiceTest extends TestCase
{
    use ProphecyTrait;

    private TicketService $ticketService;
    private ObjectProphecy $eventManager;
    private ObjectProphecy $entityManager;
    private ObjectProphecy $organisationManager;
    private ObjectProphecy $siteManager;
    private ObjectProphecy $contactService;
    private ObjectProphecy $queueManager;
    private ObjectProphecy $userManager;
    private ObjectProphecy $mailService;

    protected function setUp(): void
    {
        $this->eventManager        = $this->prophesize(EventManagerInterface::class);
        $this->entityManager       = $this->prophesize(EntityManager::class);
        $this->organisationManager = $this->prophesize(OrganisationManager::class);
        $this->siteManager         = $this->prophesize(SiteManager::class);
        $this->contactService      = $this->prophesize(ContactService::class);
        $this->queueManager        = $this->prophesize(QueueManager::class);
        $this->userManager         = $this->prophesize(UserManager::class);
        $this->mailService         = $this->prophesize(MailService::class);

        $this->ticketService = new TicketService(
            $this->eventManager->reveal(),
            $this->entityManager->reveal(),
            $this->organisationManager->reveal(),
            $this->siteManager->reveal(),
            $this->contactService->reveal(),
            $this->queueManager->reveal(),
            $this->userManager->reveal(),
            $this->mailService->reveal()
        );
    }

    public function testGetOrganisationByUuid(): void
    {
        $uuid         = 'test-org-uuid';
        $organisation = $this->createMock(Organisation::class);

        $this->organisationManager->findOrganisationByUuid($uuid)
            ->willReturn($organisation);

        $result = $this->ticketService->getOrganisationByUuid($uuid);

        $this->assertSame($organisation, $result);
    }

    public function testGetOrganisationById(): void
    {
        $id           = 123;
        $organisation = $this->createMock(Organisation::class);

        $this->organisationManager->findOrganisationById($id)
            ->willReturn($organisation);

        $result = $this->ticketService->getOrganisationById($id);

        $this->assertSame($organisation, $result);
    }

    public function testFindSiteById(): void
    {
        $id   = 456;
        $site = $this->createMock(SiteEntity::class);

        $this->siteManager->fetchSiteById($id)
            ->willReturn($site);

        $result = $this->ticketService->findSiteById($id);

        $this->assertSame($site, $result);
    }

    public function testGetSitesByOrganisationId(): void
    {
        $id    = 789;
        $sites = [$this->createMock(SiteEntity::class)];

        $this->siteManager->fetchSitesByOrganisationId($id)
            ->willReturn($sites);

        $result = $this->ticketService->getSitesByOrganisationId($id);

        $this->assertSame($sites, $result);
    }

    public function testGetContactsByOrganisationId(): void
    {
        $id       = 101;
        $contacts = [$this->createMock(Contact::class)];

        $this->contactService->fetchContactsByOrganisationId($id)
            ->willReturn($contacts);

        $result = $this->ticketService->getContactsByOrganisationId($id);

        $this->assertSame($contacts, $result);
    }

    public function testFindContactById(): void
    {
        $id      = 202;
        $contact = $this->createMock(Contact::class);

        $this->contactService->findContactById($id)
            ->willReturn($contact);

        $result = $this->ticketService->findContactById($id);

        $this->assertSame($contact, $result);
    }

    public function testFindQueueById(): void
    {
        $id    = 303;
        $queue = $this->createMock(Queue::class);

        $this->queueManager->findQueueById($id)
            ->willReturn($queue);

        $result = $this->ticketService->findQueueById($id);

        $this->assertSame($queue, $result);
    }

    public function testFindUserById(): void
    {
        $id   = 404;
        $user = $this->createMock(User::class);

        $this->userManager->findById($id)
            ->willReturn($user);

        $result = $this->ticketService->findUserById($id);

        $this->assertSame($user, $result);
    }

    public function testFindPriorityById(): void
    {
        $id         = 505;
        $priority   = $this->createMock(Priority::class);
        $repository = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(Priority::class)
            ->willReturn($repository->reveal());
        $repository->find($id)->willReturn($priority);

        $result = $this->ticketService->findPriorityById($id);

        $this->assertSame($priority, $result);
    }

    public function testFindTypeById(): void
    {
        $id         = 606;
        $type       = $this->createMock(Type::class);
        $repository = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(Type::class)
            ->willReturn($repository->reveal());
        $repository->find($id)->willReturn($type);

        $result = $this->ticketService->findTypeById($id);

        $this->assertSame($type, $result);
    }

    public function testFindStatusById(): void
    {
        $id         = 707;
        $status     = $this->createMock(Status::class);
        $repository = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(Status::class)
            ->willReturn($repository->reveal());
        $repository->find($id)->willReturn($status);

        $result = $this->ticketService->findStatusById($id);

        $this->assertSame($status, $result);
    }

    public function testFindTicketById(): void
    {
        $id         = 808;
        $ticket     = $this->createMock(Ticket::class);
        $repository = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(Ticket::class)
            ->willReturn($repository->reveal());
        $repository->find($id)->willReturn($ticket);

        $result = $this->ticketService->findTicketById($id);

        $this->assertSame($ticket, $result);
    }

    public function testGetQueues(): void
    {
        $queues = [$this->createMock(Queue::class)];

        $this->queueManager->findAll()
            ->willReturn($queues);

        $result = $this->ticketService->getQueues();

        $this->assertSame($queues, $result);
    }

    public function testGetTicketByUuid(): void
    {
        $uuid       = 'ticket-uuid-123';
        $ticket     = $this->createMock(Ticket::class);
        $repository = $this->prophesize(TicketRepository::class);

        $this->entityManager->getRepository(Ticket::class)
            ->willReturn($repository->reveal());
        $repository->findTicketByUuid($uuid)->willReturn($ticket);

        $result = $this->ticketService->getTicketByUuid($uuid);

        $this->assertSame($ticket, $result);
    }

    public function testSaveNewTicketCreatesTicketWithDefaults(): void
    {
        $data = [
            'short_description' => 'Test ticket',
            'long_description'  => 'Detailed description',
            'impact'            => Ticket::IMPACT_HIGH,
            'urgency'           => Ticket::URGENCY_HIGH,
            'source'            => Ticket::SOURCE_EMAIL,
            'queue_id'          => 1,
            'organisation_id'   => 2,
            'contact_id'        => 3,
            'type_id'           => 4,
        ];

        // Mock all the dependencies
        $priority = $this->createMock(Priority::class);
        $priority->method('getId')->willReturn(2); // impact + urgency

        $queue        = $this->createMock(Queue::class);
        $organisation = $this->createMock(Organisation::class);
        $organisation->method('hasSla')->willReturn(false);

        $contact = $this->createMock(Contact::class);
        $type    = $this->createMock(Type::class);
        $status  = $this->createMock(Status::class);

        $priorityRepo = $this->prophesize(EntityRepository::class);
        $statusRepo   = $this->prophesize(EntityRepository::class);
        $ticketRepo   = $this->prophesize(TicketRepository::class);

        // Setup entity manager mocks
        $this->entityManager->getRepository(Priority::class)
            ->willReturn($priorityRepo->reveal());
        $this->entityManager->getRepository(Status::class)
            ->willReturn($statusRepo->reveal());
        $this->entityManager->getRepository(Ticket::class)
            ->willReturn($ticketRepo->reveal());

        // Setup repository returns
        $priorityRepo->find(2)->willReturn($priority); // impact + urgency = 2
        $statusRepo->find(1)->willReturn($status);

        // Setup service returns
        $this->queueManager->findQueueById(1)->willReturn($queue);
        $this->organisationManager->findOrganisationById(2)->willReturn($organisation);
        $this->contactService->findContactById(3)->willReturn($contact);
        $typeRepo = $this->prophesize(EntityRepository::class);
        $this->entityManager->getRepository(Type::class)->willReturn($typeRepo->reveal());
        $typeRepo->find(4)->willReturn($type);

        $savedTicket = $this->createMock(Ticket::class);
        $savedTicket->method('getId')->willReturn(999);
        $ticketRepo->save(Argument::type(Ticket::class))->willReturn($savedTicket);

        $this->eventManager->trigger('ticket.created', $this->ticketService, ['id' => 999])
            ->shouldBeCalled();

        $result = $this->ticketService->save($data);

        $this->assertSame($savedTicket, $result);
    }

    public function testUpdateStatusChangesTicketStatus(): void
    {
        $ticketId = 123;
        $statusId = Status::STATUS_RESOLVED;

        $ticket = $this->createMock(Ticket::class);
        $status = $this->createMock(Status::class);
        $status->method('getId')->willReturn($statusId);

        $statusRepo = $this->prophesize(EntityRepository::class);
        $ticketRepo = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(Status::class)
            ->willReturn($statusRepo->reveal());
        $this->entityManager->getRepository(Ticket::class)
            ->willReturn($ticketRepo->reveal());

        $statusRepo->find($statusId)->willReturn($status);
        $ticketRepo->find($ticketId)->willReturn($ticket);

        $ticket->expects($this->once())->method('setStatus')->with($status)->willReturn($ticket);
        $ticket->expects($this->once())
            ->method('setLastResponseDate')
            ->with($this->isType('string'))
            ->willReturn($ticket);

        $this->entityManager->flush()->shouldBeCalled();

        $result = $this->ticketService->updateStatus($ticketId, $statusId);

        $this->assertSame($status, $result);
    }

    public function testSendNotificationEmailWithinThreshold(): void
    {
        $ticket       = $this->createMock(Ticket::class);
        $contact      = $this->createMock(Contact::class);
        $organisation = $this->createMock(Organisation::class);
        $queue        = $this->createMock(Queue::class);
        $agent        = $this->createMock(Agent::class);

        // Set up ticket data
        $now          = Carbon::now('UTC');
        $dueDate      = $now->copy()->addHour(); // Due in 1 hour
        $lastNotified = $now->copy()->subHours(2); // Last notified 2 hours ago

        $ticket->method('getId')->willReturn(123);
        $ticket->method('getDueDate')->willReturn($dueDate->format('Y-m-d H:i:s'));
        $ticket->method('getLastNotified')->willReturn($lastNotified->format('Y-m-d H:i:s'));
        $ticket->method('getShortDescription')->willReturn('Test ticket');
        $ticket->method('getContact')->willReturn($contact);
        $ticket->method('getOrganisation')->willReturn($organisation);
        $ticket->method('getQueue')->willReturn($queue);

        $contact->method('getFirstName')->willReturn('John');
        $organisation->method('getName')->willReturn('Test Org');
        $queue->method('getMembers')->willReturn(new ArrayCollection([$agent]));
        $agent->method('getEmail')->willReturn('agent@example.com');

        $ticket->expects($this->once())->method('setLastNotified')->with($this->isType('string'))->willReturn($ticket);
        $this->entityManager->flush()->shouldBeCalled();

        $this->mailService->send(
            'agent@example.com',
            Argument::containingString('Ticket 123 due in'),
            Argument::containingString('Ticket 123 (Test ticket)')
        )->shouldBeCalled();

        $this->ticketService->sendNotificationEmail($ticket, 30, TicketService::DUE_PERIOD_MINUTES);
    }

    public function testNewTicketNotificationSendsEmailToQueueMembers(): void
    {
        $ticket = $this->createMock(Ticket::class);
        $queue  = $this->createMock(Queue::class);
        $agent1 = $this->createMock(Agent::class);
        $agent2 = $this->createMock(Agent::class);

        $ticket->method('getId')->willReturn(456);
        $ticket->method('getQueue')->willReturn($queue);

        $queue->method('getMembers')->willReturn(new ArrayCollection([$agent1, $agent2]));
        $agent1->method('getEmail')->willReturn('agent1@example.com');
        $agent2->method('getEmail')->willReturn('agent2@example.com');

        $this->mailService->send(
            'agent1@example.com',
            'New ticket notification',
            'A new ticket has been created, ticket #456'
        )
            ->shouldBeCalled();
        $this->mailService->send(
            'agent2@example.com',
            'New ticket notification',
            'A new ticket has been created, ticket #456'
        )
            ->shouldBeCalled();

        $this->ticketService->newTicketNotification($ticket);
    }

    public function testConstantsHaveCorrectValues(): void
    {
        $this->assertEquals(1, TicketService::DUE_PERIOD_MINUTES);
        $this->assertEquals(2, TicketService::DUE_PERIOD_HOURS);
        $this->assertEquals(3, TicketService::DUE_PERIOD_DAYS);
        $this->assertEquals(4, TicketService::DUE_PERIOD_WEEKS);
        $this->assertEquals(5, TicketService::DUE_PERIOD_MONTHS);
    }

    public function testFindAgentFromId(): void
    {
        $id         = 999;
        $agent      = $this->createMock(Agent::class);
        $repository = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(Agent::class)
            ->willReturn($repository->reveal());
        $repository->find($id)->willReturn($agent);

        $result = $this->ticketService->findAgentFromId($id);

        $this->assertSame($agent, $result);
    }

    public function testGetEntityManager(): void
    {
        $result = $this->ticketService->getEntityManager();

        $this->assertSame($this->entityManager->reveal(), $result);
    }

    /**
     * @dataProvider duePeriodProvider
     */
    public function testSendNotificationEmailWithDifferentPeriods(int $period, string $method): void
    {
        $ticket       = $this->createMock(Ticket::class);
        $contact      = $this->createMock(Contact::class);
        $organisation = $this->createMock(Organisation::class);
        $queue        = $this->createMock(Queue::class);

        $now          = Carbon::now('UTC');
        $dueDate      = $now->copy()->addDay(); // Due tomorrow
        $lastNotified = $now->copy()->subDays(2); // Last notified 2 days ago

        $ticket->method('getId')->willReturn(123);
        $ticket->method('getDueDate')->willReturn($dueDate->format('Y-m-d H:i:s'));
        $ticket->method('getLastNotified')->willReturn($lastNotified->format('Y-m-d H:i:s'));
        $ticket->method('getShortDescription')->willReturn('Test');
        $ticket->method('getContact')->willReturn($contact);
        $ticket->method('getOrganisation')->willReturn($organisation);
        $ticket->method('getQueue')->willReturn($queue);

        $contact->method('getFirstName')->willReturn('Test');
        $organisation->method('getName')->willReturn('Test Org');
        $queue->method('getMembers')->willReturn(new ArrayCollection([]));

        // Mock the ticket methods that might be called (conditional based on notification logic)
        $ticket->method('setLastNotified')->with($this->isType('string'))->willReturn($ticket);
        $this->entityManager->flush()->willReturn(null);

        // Test that it processes different time periods without error
        $this->ticketService->sendNotificationEmail($ticket, 1, $period);

        // Assert that the method completed without exception
        $this->assertTrue(true);
    }

    public function duePeriodProvider(): array
    {
        return [
            'minutes' => [TicketService::DUE_PERIOD_MINUTES, 'subMinutes'],
            'hours'   => [TicketService::DUE_PERIOD_HOURS, 'subHours'],
            'days'    => [TicketService::DUE_PERIOD_DAYS, 'subDays'],
            'weeks'   => [TicketService::DUE_PERIOD_WEEKS, 'subWeeks'],
            'months'  => [TicketService::DUE_PERIOD_MONTHS, 'subMonths'],
        ];
    }
}
