<?php

declare(strict_types=1);

namespace TicketTest\Command;

use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Organisation\Entity\Organisation;
use OrganisationContact\Entity\Contact;
use PHPUnit\Framework\TestCase;
use ServiceLevel\Entity\BusinessHours;
use ServiceLevel\Entity\Sla;
use ServiceLevel\Entity\SlaTarget;
use Ticket\Command\CreateTicketsFromEmail;
use Ticket\Entity\Queue;
use Ticket\Entity\Ticket;
use Ticket\Service\TicketService;

final class CreateTicketsFromEmailTest extends TestCase
{
    public function testCreateTicketFromMessageHandlesSlaPriority(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $ticketService = $this->createMock(TicketService::class);
        $logger        = new Logger('test', [new TestHandler()]);

        $config = [
            'encryption' => ['key' => 'test-key'],
            'mail'       => ['sender' => 'helpdesk@example.com'],
        ];

        $command = new class ($entityManager, $ticketService, $logger, $config) extends CreateTicketsFromEmail {
            public function callCreateTicketFromMessage(array $message, Queue $queue): ?int
            {
                return $this->createTicketFromMessage($message, $queue);
            }
        };

        $contactRepo = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with(Contact::class)
            ->willReturn($contactRepo);

        $contact = $this->createMock(Contact::class);
        $contactRepo->expects($this->once())
            ->method('findOneBy')
            ->willReturn($contact);

        $organisation = $this->createMock(Organisation::class);
        $contact->method('getId')->willReturn(7);
        $contact->method('getOrganisation')->willReturn($organisation);

        $organisation->method('getId')->willReturn(9);
        $organisation->method('hasSla')->willReturn(true);

        $sla = $this->createMock(Sla::class);
        $organisation->method('getSla')->willReturn($sla);

        $businessHours = (new BusinessHours())
            ->setName('Default')
            ->setTimezone('UTC')
            ->setMonStart('09:00')->setMonEnd('17:00')->setMonActive(true)
            ->setTueStart('09:00')->setTueEnd('17:00')->setTueActive(true)
            ->setWedStart('09:00')->setWedEnd('17:00')->setWedActive(true)
            ->setThuStart('09:00')->setThuEnd('17:00')->setThuActive(true)
            ->setFriStart('09:00')->setFriEnd('17:00')->setFriActive(true)
            ->setSatActive(false)
            ->setSunActive(false);

        $sla->method('getBusinessHours')->willReturn($businessHours);

        $slaTarget = $this->createMock(SlaTarget::class);
        $slaTarget->method('getResolveTime')->willReturn('02:00');

        $sla->expects($this->exactly(2))
            ->method('getSlaTarget')
            ->with($this->equalTo(6))
            ->willReturn($slaTarget);

        $queue = $this->createMock(Queue::class);
        $queue->method('getId')->willReturn(5);

        $savedTicket = $this->createMock(Ticket::class);
        $savedTicket->method('getId')->willReturn(321);

        $ticketService->expects($this->once())
            ->method('save')
            ->with($this->callback(function (array $data): bool {
                return $data['impact'] === 3
                    && $data['urgency'] === 3
                    && $data['queue_id'] === 5;
            }))
            ->willReturn($savedTicket);

        $message = [
            'from'    => 'sam@sheridan.co.uk',
            'date'    => Carbon::now('UTC')->toDateTimeString(),
            'subject' => 'Email subject',
            'body'    => 'Email body',
        ];

        $result = $command->callCreateTicketFromMessage($message, $queue);

        $this->assertSame(321, $result);
    }
}
