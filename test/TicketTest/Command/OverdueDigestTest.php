<?php

declare(strict_types=1);

namespace TicketTest\Command;

use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use MailService\Service\MailService;
use Monolog\Logger;
use Organisation\Entity\Organisation;
use OrganisationContact\Entity\Contact;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ticket\Command\OverdueDigest;
use Ticket\Entity\Agent;
use Ticket\Entity\Queue;
use Ticket\Entity\Status;
use Ticket\Entity\Ticket;
use Ticket\Repository\TicketRepository;
use Ticket\Service\TicketService;
use User\Entity\User;

use function sprintf;

class OverdueDigestTest extends TestCase
{
    private TicketService $ticketService;
    private EntityManagerInterface $entityManager;
    private TicketRepository $ticketRepository;
    private MailService $mailService;
    private Logger $logger;
    private OutputInterface $output;
    private InputInterface $input;

    protected function setUp(): void
    {
        $this->ticketService    = $this->createMock(TicketService::class);
        $this->entityManager    = $this->createMock(EntityManagerInterface::class);
        $this->ticketRepository = $this->createMock(TicketRepository::class);
        $this->mailService      = $this->createMock(MailService::class);
        $this->logger           = $this->createMock(Logger::class);
        $this->output           = $this->createMock(OutputInterface::class);
        $this->input            = $this->createMock(InputInterface::class);

        $this->ticketService->method('getEntityManager')->willReturn($this->entityManager);
        $this->entityManager->method('getRepository')
            ->with(Ticket::class)
            ->willReturn($this->ticketRepository);
    }

    public function testExecuteWithNoOverdueTicketsOutputsMessage(): void
    {
        $this->ticketRepository->method('findOverdueTicketsForDigest')->willReturn([]);

        $this->output->expects($this->once())
            ->method('writeln')
            ->with('<comment>No overdue tickets</comment>');

        $command = new OverdueDigest($this->ticketService, $this->mailService, $this->logger);
        $result  = $command->execute($this->input, $this->output);

        $this->assertSame(Command::SUCCESS, $result);
    }

    public function testExecuteSendsDigestToQueueMembers(): void
    {
        $queue   = $this->createMock(Queue::class);
        $member  = $this->createMock(Agent::class);
        $member2 = $this->createMock(Agent::class);

        $queue->method('getId')->willReturn(10);
        $queue->method('getName')->willReturn('Support');
        $queue->method('getMembers')->willReturn(new ArrayCollection([$member, $member2]));

        $member->method('getEmail')->willReturn('agent1@example.com');
        $member2->method('getEmail')->willReturn('agent2@example.com');

        $tickets = [
            $this->createOverdueTicket(1001, $queue),
            $this->createOverdueTicket(1002, $queue),
        ];

        $this->ticketRepository->method('findOverdueTicketsForDigest')->willReturn($tickets);

        $this->mailService->expects($this->once())
            ->method('prepareBody')
            ->with(
                'ticket_mail::ticket_overdue_digest',
                $this->callback(function (array $payload): bool {
                    return $payload['queueName'] === 'Support'
                        && $payload['tickets'] !== []
                        && isset($payload['generatedAt']);
                })
            )
            ->willReturn('<html>digest</html>');

        $sent = [];
        $this->mailService->expects($this->exactly(2))
            ->method('send')
            ->willReturnCallback(function (string $to, string $subject, string $body) use (&$sent): void {
                $sent[] = [$to, $subject, $body];
            });

        $command = new OverdueDigest($this->ticketService, $this->mailService, $this->logger);
        $result  = $command->execute($this->input, $this->output);

        $this->assertSame(Command::SUCCESS, $result);
        $this->assertCount(2, $sent);
        $this->assertSame('Overdue tickets digest - Support (2)', $sent[0][1]);
        $this->assertSame('<html>digest</html>', $sent[0][2]);
    }

    public function testExecuteSkipsQueueWithNoMembers(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->method('getId')->willReturn(10);
        $queue->method('getName')->willReturn('Support');
        $queue->method('getMembers')->willReturn(new ArrayCollection([]));

        $this->ticketRepository->method('findOverdueTicketsForDigest')
            ->willReturn([$this->createOverdueTicket(1001, $queue)]);

        $this->mailService->expects($this->never())->method('send');
        $this->logger->expects($this->once())
            ->method('warning')
            ->with('Overdue digest skipped for queue with no members', ['queue' => 'Support']);

        $command = new OverdueDigest($this->ticketService, $this->mailService, $this->logger);
        $result  = $command->execute($this->input, $this->output);

        $this->assertSame(Command::SUCCESS, $result);
    }

    public function testExecuteSendsDigestPerQueue(): void
    {
        $queueA = $this->createMock(Queue::class);
        $queueB = $this->createMock(Queue::class);
        $member = $this->createMock(Agent::class);

        $queueA->method('getId')->willReturn(10);
        $queueA->method('getName')->willReturn('Support');
        $queueA->method('getMembers')->willReturn(new ArrayCollection([$member]));

        $queueB->method('getId')->willReturn(11);
        $queueB->method('getName')->willReturn('Sales');
        $queueB->method('getMembers')->willReturn(new ArrayCollection([$member]));

        $member->method('getEmail')->willReturn('agent@example.com');

        $this->ticketRepository->method('findOverdueTicketsForDigest')->willReturn([
            $this->createOverdueTicket(1001, $queueA),
            $this->createOverdueTicket(1002, $queueB),
        ]);

        $seenQueues = [];
        $this->mailService->expects($this->exactly(2))
            ->method('prepareBody')
            ->willReturnCallback(function (string $template, array $payload) use (&$seenQueues): string {
                $seenQueues[] = $payload['queueName'] ?? '';
                return '<html>digest</html>';
            });

        $this->mailService->expects($this->exactly(2))
            ->method('send')
            ->with(
                'agent@example.com',
                $this->stringContains('Overdue tickets digest'),
                '<html>digest</html>'
            );

        $command = new OverdueDigest($this->ticketService, $this->mailService, $this->logger);
        $result  = $command->execute($this->input, $this->output);

        $this->assertSame(Command::SUCCESS, $result);
        $this->assertEquals(['Support', 'Sales'], $seenQueues);
    }

    private function createOverdueTicket(int $id, Queue $queue): Ticket
    {
        $ticket       = $this->createMock(Ticket::class);
        $status       = $this->createMock(Status::class);
        $agent        = $this->createMock(User::class);
        $contact      = $this->createMock(Contact::class);
        $organisation = $this->createMock(Organisation::class);

        $status->method('getDescription')->willReturn('Open');
        $agent->method('getFullName')->willReturn('Agent Smith');
        $contact->method('getFirstName')->willReturn('John');
        $contact->method('getLastName')->willReturn('Doe');
        $organisation->method('getName')->willReturn('Acme');

        $ticket->method('getId')->willReturn($id);
        $ticket->method('getShortDescription')->willReturn(sprintf('Ticket %d', $id));
        $ticket->method('getDueDate')->willReturn(
            Carbon::now('UTC')->subHour()->format('Y-m-d H:i:s')
        );
        $ticket->method('getQueue')->willReturn($queue);
        $ticket->method('getAssignedAgent')->willReturn($agent);
        $ticket->method('getStatus')->willReturn($status);
        $ticket->method('getContact')->willReturn($contact);
        $ticket->method('getOrganisation')->willReturn($organisation);

        return $ticket;
    }
}
