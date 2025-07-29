<?php

declare(strict_types=1);

namespace OrganisationTest\Entity;

use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Doctrine\Common\Collections\Collection;
use Organisation\Entity\Domain;
use Organisation\Entity\Organisation;
use Organisation\Entity\OrganisationInterface;
use Organisation\Exception\OrganisationNameException;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;
use ServiceLevel\Entity\Sla;

class OrganisationTest extends TestCase
{
    private Organisation $organisation;

    protected function setUp(): void
    {
        $this->organisation = new Organisation();
    }

    public function testConstructorSetsDefaults(): void
    {
        $organisation = new Organisation();

        $this->assertNull($organisation->getId());
        $this->assertEquals(Organisation::STATE_ACTIVE, $organisation->getIsActive());
        $this->assertEquals(Organisation::TYPE_CLIENT, $organisation->getTypeId());
        $this->assertInstanceOf(UuidInterface::class, $organisation->getUuid());
        $this->assertInstanceOf(DateTime::class, $organisation->getCreated());
        $this->assertInstanceOf(DateTime::class, $organisation->getModified());
        $this->assertCount(0, $organisation->getDomains());
    }

    public function testImplementsOrganisationInterface(): void
    {
        $this->assertInstanceOf(OrganisationInterface::class, $this->organisation);
    }

    public function testSetAndGetId(): void
    {
        $result = $this->organisation->setId(123);

        $this->assertInstanceOf(Organisation::class, $result);
        $this->assertEquals(123, $this->organisation->getId());
    }

    public function testSetAndGetUuid(): void
    {
        $uuid   = $this->createMock(UuidInterface::class);
        $result = $this->organisation->setUuid($uuid);

        $this->assertInstanceOf(Organisation::class, $result);
        $this->assertSame($uuid, $this->organisation->getUuid());
    }

    public function testSetCreatedWhenNewOrganisation(): void
    {
        // When ID is null (new organisation), it should set current time
        Carbon::setTestNow('2023-01-01 12:00:00');

        $testDate = new DateTime('2020-01-01 10:00:00', new DateTimeZone('UTC'));
        $result   = $this->organisation->setCreated($testDate);

        $this->assertInstanceOf(Organisation::class, $result);
        // Should ignore the provided date and use current time for new organisations
        $this->assertEquals('2023-01-01 12:00:00', $this->organisation->getCreated()->format('Y-m-d H:i:s'));

        Carbon::setTestNow(); // Reset
    }

    public function testSetCreatedWhenExistingOrganisation(): void
    {
        // Set ID to make it an existing organisation
        $this->organisation->setId(123);

        $testDate = new DateTime('2020-01-01 10:00:00', new DateTimeZone('UTC'));
        $result   = $this->organisation->setCreated($testDate);

        $this->assertInstanceOf(Organisation::class, $result);
        $this->assertEquals('2020-01-01 10:00:00', $this->organisation->getCreated()->format('Y-m-d H:i:s'));
    }

    /**
     * @dataProvider activeStateProvider
     */
    public function testSetAndGetIsActive(int $state): void
    {
        $result = $this->organisation->setIsActive($state);

        $this->assertInstanceOf(Organisation::class, $result);
        $this->assertEquals($state, $this->organisation->getIsActive());
    }

    public function activeStateProvider(): array
    {
        return [
            'inactive' => [Organisation::STATE_INACTIVE],
            'active'   => [Organisation::STATE_ACTIVE],
            'disabled' => [Organisation::STATE_DISABLED],
        ];
    }

    public function testSetModifiedWithNullUsesCurrentTime(): void
    {
        Carbon::setTestNow('2023-01-01 15:30:00');

        $result = $this->organisation->setModified();

        $this->assertInstanceOf(Organisation::class, $result);
        $this->assertEquals('2023-01-01 15:30:00', $this->organisation->getModified()->format('Y-m-d H:i:s'));

        Carbon::setTestNow(); // Reset
    }

    public function testSetModifiedWithSpecificDate(): void
    {
        $testDate = new DateTime('2022-06-15 09:45:00', new DateTimeZone('UTC'));
        $result   = $this->organisation->setModified($testDate);

        $this->assertInstanceOf(Organisation::class, $result);
        $this->assertEquals('2022-06-15 09:45:00', $this->organisation->getModified()->format('Y-m-d H:i:s'));
    }

    public function testSetAndGetNameWithValidName(): void
    {
        $name   = 'Test Organisation Ltd';
        $result = $this->organisation->setName($name);

        $this->assertInstanceOf(Organisation::class, $result);
        $this->assertEquals($name, $this->organisation->getName());
    }

    public function testSetNameWithInvalidNameThrowsException(): void
    {
        $this->expectException(OrganisationNameException::class);

        // Empty name should be invalid
        $this->organisation->setName('');
    }

    /**
     * @dataProvider typeProvider
     */
    public function testSetAndGetTypeId(int $typeId): void
    {
        $result = $this->organisation->setTypeId($typeId);

        $this->assertInstanceOf(Organisation::class, $result);
        $this->assertEquals($typeId, $this->organisation->getTypeId());
    }

    public function typeProvider(): array
    {
        return [
            'client'   => [Organisation::TYPE_CLIENT],
            'supplier' => [Organisation::TYPE_SUPPLIER],
            'both'     => [Organisation::TYPE_BOTH],
        ];
    }

    public function testGetDomainsReturnsCollection(): void
    {
        $domains = $this->organisation->getDomains();

        $this->assertInstanceOf(Collection::class, $domains);
        $this->assertCount(0, $domains);
    }

    public function testAddDomainAddsNewDomain(): void
    {
        $domain = $this->createMock(Domain::class);

        $this->organisation->addDomain($domain);

        $this->assertCount(1, $this->organisation->getDomains());
        $this->assertTrue($this->organisation->hasDomain($domain));
    }

    public function testAddDomainDoesNotAddDuplicateDomain(): void
    {
        $domain = new Domain();

        $this->organisation->addDomain($domain);
        $this->organisation->addDomain($domain); // Try to add same domain again

        $this->assertCount(1, $this->organisation->getDomains());
    }

    public function testHasDomainReturnsTrueForExistingDomain(): void
    {
        $domain = $this->createMock(Domain::class);

        $this->organisation->addDomain($domain);

        $this->assertTrue($this->organisation->hasDomain($domain));
    }

    public function testHasDomainReturnsFalseForNonExistentDomain(): void
    {
        $domain = $this->createMock(Domain::class);

        $this->assertFalse($this->organisation->hasDomain($domain));
    }

    public function testRemoveDomainRemovesExistingDomain(): void
    {
        $domain = new Domain();

        $this->organisation->addDomain($domain);
        $this->assertCount(1, $this->organisation->getDomains());

        $this->organisation->removeDomain($domain);
        $this->assertCount(0, $this->organisation->getDomains());
    }

    public function testSetAndGetSla(): void
    {
        $sla    = $this->createMock(Sla::class);
        $result = $this->organisation->setSla($sla);

        $this->assertInstanceOf(Organisation::class, $result);
        $this->assertSame($sla, $this->organisation->getSla());
    }

    public function testGetSlaReturnsNullByDefault(): void
    {
        $this->assertNull($this->organisation->getSla());
    }

    public function testHasSlaReturnsTrueWhenSlaIsSet(): void
    {
        $sla = $this->createMock(Sla::class);
        $this->organisation->setSla($sla);

        $this->assertTrue($this->organisation->hasSla());
    }

    public function testHasSlaReturnsFalseWhenSlaIsNull(): void
    {
        $this->assertFalse($this->organisation->hasSla());
    }

    public function testToStringReturnsName(): void
    {
        $name = 'Test Company Inc';
        $this->organisation->setName($name);

        $this->assertEquals($name, (string) $this->organisation);
    }

    public function testFluentInterfaceChaining(): void
    {
        $uuid     = $this->createMock(UuidInterface::class);
        $sla      = $this->createMock(Sla::class);
        $testDate = new DateTime('2023-01-01', new DateTimeZone('UTC'));

        $result = $this->organisation
            ->setId(456)
            ->setUuid($uuid)
            ->setName('Chained Organisation')
            ->setTypeId(Organisation::TYPE_SUPPLIER)
            ->setIsActive(Organisation::STATE_DISABLED)
            ->setModified($testDate)
            ->setSla($sla);

        $this->assertInstanceOf(Organisation::class, $result);
        $this->assertEquals(456, $this->organisation->getId());
        $this->assertSame($uuid, $this->organisation->getUuid());
        $this->assertEquals('Chained Organisation', $this->organisation->getName());
        $this->assertEquals(Organisation::TYPE_SUPPLIER, $this->organisation->getTypeId());
        $this->assertEquals(Organisation::STATE_DISABLED, $this->organisation->getIsActive());
        $this->assertEquals('2023-01-01 00:00:00', $this->organisation->getModified()->format('Y-m-d H:i:s'));
        $this->assertSame($sla, $this->organisation->getSla());
    }

    public function testConstantsHaveCorrectValues(): void
    {
        // State constants
        $this->assertEquals(0, Organisation::STATE_INACTIVE);
        $this->assertEquals(1, Organisation::STATE_ACTIVE);
        $this->assertEquals(2, Organisation::STATE_DISABLED);

        // Type constants
        $this->assertEquals(1, Organisation::TYPE_CLIENT);
        $this->assertEquals(2, Organisation::TYPE_SUPPLIER);
        $this->assertEquals(3, Organisation::TYPE_BOTH);
    }

    public function testDomainCollectionOperations(): void
    {
        $domain1 = new Domain();
        $domain2 = new Domain();
        $domain3 = new Domain();

        // Add multiple domains
        $this->organisation->addDomain($domain1);
        $this->organisation->addDomain($domain2);
        $this->organisation->addDomain($domain3);

        $this->assertCount(3, $this->organisation->getDomains());

        // Remove one domain
        $this->organisation->removeDomain($domain2);
        $this->assertCount(2, $this->organisation->getDomains());
        $this->assertFalse($this->organisation->hasDomain($domain2));
        $this->assertTrue($this->organisation->hasDomain($domain1));
        $this->assertTrue($this->organisation->hasDomain($domain3));
    }

    /**
     * @dataProvider invalidNameProvider
     */
    public function testSetNameWithVariousInvalidInputs(string $invalidName): void
    {
        $this->expectException(OrganisationNameException::class);

        $this->organisation->setName($invalidName);
    }

    public function invalidNameProvider(): array
    {
        return [
            'empty string'       => [''],
            'special characters' => ['Test@Company!'],
            'symbols'            => ['Company & Co.'],
            'unicode characters' => ['Tëst Compañy'],
        ];
    }

    public function testSetIsActiveLogicWithInactiveDefault(): void
    {
        // Create organisation with inactive state initially
        $organisation = new Organisation();
        $organisation->setIsActive(Organisation::STATE_INACTIVE);

        // The logic in setIsActive forces inactive (0) to become active (1)
        $result = $organisation->setIsActive(Organisation::STATE_DISABLED);

        // Due to the weird logic in the setter, if current state is 0, it forces to 1
        // Since we set it to inactive (0) first, when we try to set DISABLED (2), it becomes 1
        $this->assertEquals(Organisation::STATE_ACTIVE, $organisation->getIsActive());
    }
}
