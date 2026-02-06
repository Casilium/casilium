<?php

declare(strict_types=1);

namespace TicketTest\Entity;

use Carbon\Carbon;
use Organisation\Entity\Organisation;
use OrganisationContact\Entity\Contact;
use OrganisationSite\Entity\SiteEntity;
use PHPUnit\Framework\TestCase;
use ServiceLevel\Entity\SlaTarget;
use Ticket\Entity\Agent;
use Ticket\Entity\Priority;
use Ticket\Entity\Queue;
use Ticket\Entity\Status;
use Ticket\Entity\Ticket;
use Ticket\Entity\Type;
use User\Entity\User;

class TicketTest extends TestCase
{
    private Ticket $ticket;

    protected function setUp(): void
    {
        $this->ticket = new Ticket();
    }

    public function testConstructorSetsDefaults(): void
    {
        $ticket = new Ticket();

        $this->assertEquals(Ticket::IMPACT_DEFAULT, $ticket->getImpact());
        $this->assertEquals(Ticket::SOURCE_PHONE, $ticket->getSource());
        $this->assertEquals(Ticket::URGENCY_DEFAULT, $ticket->getUrgency());
        $this->assertNull($ticket->getAgent());
        $this->assertNull($ticket->getSite());
        $this->assertIsString($ticket->getUuid());
        $this->assertNotEmpty($ticket->getCreatedAt());
        $this->assertNotEmpty($ticket->getLastNotified());
    }

    public function testSetAndGetId(): void
    {
        $result = $this->ticket->setId(123);

        $this->assertInstanceOf(Ticket::class, $result);
        $this->assertEquals(123, $this->ticket->getId());
    }

    public function testSetAndGetShortDescription(): void
    {
        $description = 'Test ticket description';
        $result      = $this->ticket->setShortDescription($description);

        $this->assertInstanceOf(Ticket::class, $result);
        $this->assertEquals($description, $this->ticket->getShortDescription());
    }

    public function testSetAndGetLongDescription(): void
    {
        $description = 'This is a detailed description of the ticket issue.';
        $result      = $this->ticket->setLongDescription($description);

        $this->assertInstanceOf(Ticket::class, $result);
        $this->assertEquals($description, $this->ticket->getLongDescription());
    }

    /**
     * @dataProvider impactProvider
     */
    public function testSetAndGetImpact(int $impact): void
    {
        $result = $this->ticket->setImpact($impact);

        $this->assertInstanceOf(Ticket::class, $result);
        $this->assertEquals($impact, $this->ticket->getImpact());
    }

    public static function impactProvider(): array
    {
        return [
            'high impact'   => [Ticket::IMPACT_HIGH],
            'medium impact' => [Ticket::IMPACT_MEDIUM],
            'low impact'    => [Ticket::IMPACT_LOW],
        ];
    }

    /**
     * @dataProvider urgencyProvider
     */
    public function testSetAndGetUrgency(int $urgency): void
    {
        $result = $this->ticket->setUrgency($urgency);

        $this->assertInstanceOf(Ticket::class, $result);
        $this->assertEquals($urgency, $this->ticket->getUrgency());
    }

    public static function urgencyProvider(): array
    {
        return [
            'high urgency'   => [Ticket::URGENCY_HIGH],
            'medium urgency' => [Ticket::URGENCY_MEDIUM],
            'low urgency'    => [Ticket::URGENCY_LOW],
        ];
    }

    /**
     * @dataProvider sourceProvider
     */
    public function testSetAndGetSource(int $source): void
    {
        $result = $this->ticket->setSource($source);

        $this->assertInstanceOf(Ticket::class, $result);
        $this->assertEquals($source, $this->ticket->getSource());
    }

    public static function sourceProvider(): array
    {
        return [
            'email source' => [Ticket::SOURCE_EMAIL],
            'phone source' => [Ticket::SOURCE_PHONE],
            'web source'   => [Ticket::SOURCE_WEB],
        ];
    }

    public function testSetAndGetAssignedAgent(): void
    {
        $user   = $this->createMock(User::class);
        $result = $this->ticket->setAssignedAgent($user);

        $this->assertInstanceOf(Ticket::class, $result);
        $this->assertSame($user, $this->ticket->getAssignedAgent());
    }

    public function testSetAndGetAgent(): void
    {
        $agent  = $this->createMock(Agent::class);
        $result = $this->ticket->setAgent($agent);

        $this->assertInstanceOf(Ticket::class, $result);
        $this->assertSame($agent, $this->ticket->getAgent());
    }

    public function testSetAndGetPriority(): void
    {
        $priority = $this->createMock(Priority::class);
        $result   = $this->ticket->setPriority($priority);

        $this->assertInstanceOf(Ticket::class, $result);
        $this->assertSame($priority, $this->ticket->getPriority());
    }

    public function testSetAndGetQueue(): void
    {
        $queue  = $this->createMock(Queue::class);
        $result = $this->ticket->setQueue($queue);

        $this->assertInstanceOf(Ticket::class, $result);
        $this->assertSame($queue, $this->ticket->getQueue());
    }

    public function testSetAndGetStatus(): void
    {
        $status = $this->createMock(Status::class);
        $result = $this->ticket->setStatus($status);

        $this->assertInstanceOf(Ticket::class, $result);
        $this->assertSame($status, $this->ticket->getStatus());
    }

    public function testSetAndGetType(): void
    {
        $type   = $this->createMock(Type::class);
        $result = $this->ticket->setType($type);

        $this->assertInstanceOf(Ticket::class, $result);
        $this->assertSame($type, $this->ticket->getType());
    }

    public function testSetAndGetOrganisation(): void
    {
        $organisation = $this->createMock(Organisation::class);
        $result       = $this->ticket->setOrganisation($organisation);

        $this->assertInstanceOf(Ticket::class, $result);
        $this->assertSame($organisation, $this->ticket->getOrganisation());
    }

    public function testSetAndGetSite(): void
    {
        $site   = $this->createMock(SiteEntity::class);
        $result = $this->ticket->setSite($site);

        $this->assertInstanceOf(Ticket::class, $result);
        $this->assertSame($site, $this->ticket->getSite());
    }

    public function testSetAndGetContact(): void
    {
        $contact = $this->createMock(Contact::class);
        $result  = $this->ticket->setContact($contact);

        $this->assertInstanceOf(Ticket::class, $result);
        $this->assertSame($contact, $this->ticket->getContact());
    }

    public function testSetAndGetUuid(): void
    {
        $uuid   = 'test-uuid-123';
        $result = $this->ticket->setUuid($uuid);

        $this->assertInstanceOf(Ticket::class, $result);
        $this->assertEquals($uuid, $this->ticket->getUuid());
    }

    public function testSetAndGetCreatedAt(): void
    {
        $createdAt = '2023-01-01 12:00:00';
        $result    = $this->ticket->setCreatedAt($createdAt);

        $this->assertInstanceOf(Ticket::class, $result);
        $this->assertEquals($createdAt, $this->ticket->getCreatedAt());
    }

    public function testSetAndGetDueDate(): void
    {
        $dueDate = '2023-01-02 12:00:00';
        $result  = $this->ticket->setDueDate($dueDate);

        $this->assertInstanceOf(Ticket::class, $result);
        $this->assertEquals($dueDate, $this->ticket->getDueDate());
    }

    public function testSetAndGetLastResponseDate(): void
    {
        $date   = '2023-01-01 15:30:00';
        $result = $this->ticket->setLastResponseDate($date);

        $this->assertInstanceOf(Ticket::class, $result);
        $this->assertEquals($date, $this->ticket->getLastResponseDate());
    }

    public function testSetAndGetFirstResponseDate(): void
    {
        $date   = '2023-01-01 13:00:00';
        $result = $this->ticket->setFirstResponseDate($date);

        $this->assertInstanceOf(Ticket::class, $result);
        $this->assertEquals($date, $this->ticket->getFirstResponseDate());
    }

    public function testSetAndGetResolveDate(): void
    {
        $date   = '2023-01-03 10:00:00';
        $result = $this->ticket->setResolveDate($date);

        $this->assertInstanceOf(Ticket::class, $result);
        $this->assertEquals($date, $this->ticket->getResolveDate());
    }

    public function testSetAndGetSlaTarget(): void
    {
        $slaTarget = $this->createMock(SlaTarget::class);
        $this->ticket->setSlaTarget($slaTarget);

        $this->assertSame($slaTarget, $this->ticket->getSlaTarget());
    }

    public function testHasSlaWithSlaTarget(): void
    {
        $slaTarget = $this->createMock(SlaTarget::class);
        $this->ticket->setSlaTarget($slaTarget);

        $this->assertTrue($this->ticket->hasSla());
    }

    public function testHasSlaWithoutSlaTarget(): void
    {
        $this->assertFalse($this->ticket->hasSla());
    }

    public function testSetAndGetLastNotified(): void
    {
        $date   = '2023-01-01 16:00:00';
        $result = $this->ticket->setLastNotified($date);

        $this->assertInstanceOf(Ticket::class, $result);
        $this->assertEquals($date, $this->ticket->getLastNotified());
    }

    public function testSetAndGetCloseDate(): void
    {
        $date   = '2023-01-05 09:00:00';
        $result = $this->ticket->setCloseDate($date);

        $this->assertInstanceOf(Ticket::class, $result);
        $this->assertEquals($date, $this->ticket->getCloseDate());
    }

    public function testSetAndGetWaitingDate(): void
    {
        $date   = '2023-01-02 14:00:00';
        $result = $this->ticket->setWaitingDate($date);

        $this->assertInstanceOf(Ticket::class, $result);
        $this->assertEquals($date, $this->ticket->getWaitingDate());
    }

    public function testSetAndGetFirstResponseDue(): void
    {
        $date   = '2023-01-01 17:00:00';
        $result = $this->ticket->setFirstResponseDue($date);

        $this->assertInstanceOf(Ticket::class, $result);
        $this->assertEquals($date, $this->ticket->getFirstResponseDue());
    }

    public function testGetArrayCopyReturnsObjectVars(): void
    {
        $this->ticket->setId(123);
        $this->ticket->setShortDescription('Test');

        $result = $this->ticket->getArrayCopy();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('shortDescription', $result);
    }

    public function testExchangeArraySetsPropertiesFromArray(): void
    {
        $organisation = $this->createMock(Organisation::class);
        $slaTarget    = $this->createMock(SlaTarget::class);

        $data = [
            'id'                  => 456,
            'createdAt'           => '2023-01-01 12:00:00',
            'impact'              => Ticket::IMPACT_HIGH,
            'urgency'             => Ticket::URGENCY_HIGH,
            'short_description'   => 'Test Description',
            'long_description'    => 'Detailed description',
            'due_date'            => '2023-01-02 12:00:00',
            'last_response_date'  => '2023-01-01 15:00:00',
            'first_response_date' => '2023-01-01 14:00:00',
            'waiting_date'        => '2023-01-01 16:00:00',
            'organisation'        => $organisation,
            'sla_target'          => $slaTarget,
        ];

        $result = $this->ticket->exchangeArray($data);

        $this->assertInstanceOf(Ticket::class, $result);
        $this->assertEquals(456, $this->ticket->getId());
        $this->assertEquals('2023-01-01 12:00:00', $this->ticket->getCreatedAt());
        $this->assertEquals(Ticket::IMPACT_HIGH, $this->ticket->getImpact());
        $this->assertEquals(Ticket::URGENCY_HIGH, $this->ticket->getUrgency());
        $this->assertEquals('Test Description', $this->ticket->getShortDescription());
        $this->assertEquals('Detailed description', $this->ticket->getLongDescription());
    }

    /**
     * @dataProvider statusTextProvider
     */
    public function testGetStatusTextFromCode(int $code, string $expected): void
    {
        $this->assertEquals($expected, Ticket::getStatusTextFromCode($code));
    }

    public static function statusTextProvider(): array
    {
        return [
            'new status'  => [Ticket::STATUS_NEW, 'Open'],
            'in progress' => [Ticket::STATUS_IN_PROGRESS, 'In Progress'],
            'on hold'     => [Ticket::STATUS_ON_HOLD, 'On Hold'],
            'resolved'    => [Ticket::STATUS_RESOLVED, 'Resolved'],
            'closed'      => [Ticket::STATUS_CLOSED, 'Closed'],
        ];
    }

    /**
     * @dataProvider impactUrgencyTextProvider
     */
    public function testGetImpactUrgencyText(int $code, string $expected): void
    {
        $this->assertEquals($expected, Ticket::getImpactUrgencyText($code));
    }

    public static function impactUrgencyTextProvider(): array
    {
        return [
            'high'   => [Ticket::IMPACT_HIGH, 'High'],
            'medium' => [Ticket::IMPACT_MEDIUM, 'Medium'],
            'low'    => [Ticket::IMPACT_LOW, 'Low'],
        ];
    }

    /**
     * @dataProvider sourceTextProvider
     */
    public function testGetSourceTextFromCode(int $code, string $expected): void
    {
        $this->assertEquals($expected, Ticket::getSourceTextFromCode($code));
    }

    public static function sourceTextProvider(): array
    {
        return [
            'email' => [Ticket::SOURCE_EMAIL, 'E-Mail'],
            'phone' => [Ticket::SOURCE_PHONE, 'Phone'],
            'web'   => [Ticket::SOURCE_WEB, 'Web'],
        ];
    }

    public function testIsOverdueReturnsTrueWhenPastDue(): void
    {
        // Set due date in the past
        $pastDate = Carbon::now('UTC')->subHour()->format('Y-m-d H:i:s');
        $this->ticket->setDueDate($pastDate);

        $this->assertTrue($this->ticket->isOverdue());
    }

    public function testIsOverdueReturnsFalseWhenNotDue(): void
    {
        // Set due date in the future
        $futureDate = Carbon::now('UTC')->addHour()->format('Y-m-d H:i:s');
        $this->ticket->setDueDate($futureDate);

        $this->assertFalse($this->ticket->isOverdue());
    }

    public function testFluentInterfaceChaining(): void
    {
        $user     = $this->createMock(User::class);
        $priority = $this->createMock(Priority::class);

        $result = $this->ticket
            ->setId(123)
            ->setShortDescription('Test')
            ->setLongDescription('Detailed test')
            ->setImpact(Ticket::IMPACT_HIGH)
            ->setUrgency(Ticket::URGENCY_HIGH)
            ->setAssignedAgent($user)
            ->setPriority($priority);

        $this->assertInstanceOf(Ticket::class, $result);
        $this->assertEquals(123, $this->ticket->getId());
        $this->assertEquals('Test', $this->ticket->getShortDescription());
        $this->assertEquals('Detailed test', $this->ticket->getLongDescription());
        $this->assertEquals(Ticket::IMPACT_HIGH, $this->ticket->getImpact());
        $this->assertEquals(Ticket::URGENCY_HIGH, $this->ticket->getUrgency());
        $this->assertSame($user, $this->ticket->getAssignedAgent());
        $this->assertSame($priority, $this->ticket->getPriority());
    }

    public function testConstantsHaveCorrectValues(): void
    {
        // Impact constants
        $this->assertEquals(1, Ticket::IMPACT_HIGH);
        $this->assertEquals(2, Ticket::IMPACT_MEDIUM);
        $this->assertEquals(3, Ticket::IMPACT_LOW);
        $this->assertEquals(Ticket::IMPACT_LOW, Ticket::IMPACT_DEFAULT);

        // Source constants
        $this->assertEquals(1, Ticket::SOURCE_EMAIL);
        $this->assertEquals(2, Ticket::SOURCE_PHONE);
        $this->assertEquals(3, Ticket::SOURCE_WEB);

        // Status constants
        $this->assertEquals(1, Ticket::STATUS_NEW);
        $this->assertEquals(2, Ticket::STATUS_IN_PROGRESS);
        $this->assertEquals(3, Ticket::STATUS_ON_HOLD);
        $this->assertEquals(4, Ticket::STATUS_RESOLVED);
        $this->assertEquals(5, Ticket::STATUS_CLOSED);
        $this->assertEquals(6, Ticket::STATUS_CANCELLED);
        $this->assertEquals(Ticket::STATUS_NEW, Ticket::STATUS_DEFAULT);

        // Urgency constants
        $this->assertEquals(1, Ticket::URGENCY_HIGH);
        $this->assertEquals(2, Ticket::URGENCY_MEDIUM);
        $this->assertEquals(3, Ticket::URGENCY_LOW);
        $this->assertEquals(Ticket::URGENCY_LOW, Ticket::URGENCY_DEFAULT);
    }
}
