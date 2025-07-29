<?php

declare(strict_types=1);

namespace OrganisationSiteTest\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Organisation\Entity\Organisation;
use OrganisationSite\Entity\CountryEntity;
use OrganisationSite\Entity\SiteEntity;
use OrganisationSite\Repository\SiteRepository;
use OrganisationSite\Service\SiteManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Ramsey\Uuid\UuidInterface;

class SiteManagerTest extends TestCase
{
    use ProphecyTrait;

    private SiteManager $siteManager;
    private ObjectProphecy $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);
        $this->siteManager = new SiteManager($this->entityManager->reveal());
    }

    public function testGetOrganisationByUuid(): void
    {
        $uuid = 'test-org-uuid';
        $organisation = $this->createMock(Organisation::class);
        $repository = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(Organisation::class)
            ->willReturn($repository->reveal());
        $repository->findOneBy(['uuid' => $uuid])
            ->willReturn($organisation);

        $result = $this->siteManager->getOrganisationByUuid($uuid);

        $this->assertSame($organisation, $result);
    }

    public function testGetOrganisationByUuidReturnsNull(): void
    {
        $uuid = 'non-existent-uuid';
        $repository = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(Organisation::class)
            ->willReturn($repository->reveal());
        $repository->findOneBy(['uuid' => $uuid])
            ->willReturn(null);

        $result = $this->siteManager->getOrganisationByUuid($uuid);

        $this->assertNull($result);
    }

    public function testGetCountries(): void
    {
        $country1 = $this->createMock(CountryEntity::class);
        $country1->method('getId')->willReturn(826);
        $country1->method('getName')->willReturn('United Kingdom');

        $country2 = $this->createMock(CountryEntity::class);
        $country2->method('getId')->willReturn(840);
        $country2->method('getName')->willReturn('United States');

        $countries = [$country1, $country2];
        $repository = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(CountryEntity::class)
            ->willReturn($repository->reveal());
        $repository->findAll()->willReturn($countries);

        $result = $this->siteManager->getCountries();

        $expected = [
            826 => 'United Kingdom',
            840 => 'United States',
        ];
        $this->assertEquals($expected, $result);
    }

    public function testGetCountriesReturnsEmptyArray(): void
    {
        $repository = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(CountryEntity::class)
            ->willReturn($repository->reveal());
        $repository->findAll()->willReturn([]);

        $result = $this->siteManager->getCountries();

        $this->assertEquals([], $result);
    }

    public function testGetCountry(): void
    {
        $countryId = 826;
        $country = $this->createMock(CountryEntity::class);
        $repository = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(CountryEntity::class)
            ->willReturn($repository->reveal());
        $repository->find($countryId)->willReturn($country);

        $result = $this->siteManager->getCountry($countryId);

        $this->assertSame($country, $result);
    }

    public function testGetCountryReturnsNull(): void
    {
        $countryId = 999;
        $repository = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(CountryEntity::class)
            ->willReturn($repository->reveal());
        $repository->find($countryId)->willReturn(null);

        $result = $this->siteManager->getCountry($countryId);

        $this->assertNull($result);
    }

    public function testCreateSite(): void
    {
        $site = $this->createMock(SiteEntity::class);
        $site->method('getId')->willReturn(123);
        $site->expects($this->once())->method('setUuid')->with($this->isInstanceOf(UuidInterface::class))->willReturn($site);

        $this->entityManager->persist($site)->shouldBeCalled();
        $this->entityManager->flush()->shouldBeCalled();

        $result = $this->siteManager->createSite($site);

        $this->assertEquals(123, $result);
    }

    public function testUpdateSite(): void
    {
        $countryEntity = $this->createMock(CountryEntity::class);
        
        // Updated site data
        $updatedSite = $this->createMock(SiteEntity::class);
        $updatedSite->method('getId')->willReturn(456);
        $updatedSite->method('getName')->willReturn('Updated Site');
        $updatedSite->method('getStreetAddress')->willReturn('456 Updated Street');
        $updatedSite->method('getStreetAddress2')->willReturn('Suite B');
        $updatedSite->method('getTown')->willReturn('Updated Town');
        $updatedSite->method('getCity')->willReturn('Updated City');
        $updatedSite->method('getCounty')->willReturn('Updated County');
        $updatedSite->method('getCountry')->willReturn($countryEntity);
        $updatedSite->method('getTelephone')->willReturn('+44 20 9876 5432');

        // Current site in database
        $currentSite = $this->createMock(SiteEntity::class);
        $repository = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(SiteEntity::class)
            ->willReturn($repository->reveal());
        $repository->find(456)->willReturn($currentSite);

        // Verify all setter methods are called
        $currentSite->expects($this->once())->method('setName')->with('Updated Site')->willReturn($currentSite);
        $currentSite->expects($this->once())->method('setStreetAddress')->with('456 Updated Street')->willReturn($currentSite);
        $currentSite->expects($this->once())->method('setStreetAddress2')->with('Suite B')->willReturn($currentSite);
        $currentSite->expects($this->once())->method('setTown')->with('Updated Town')->willReturn($currentSite);
        $currentSite->expects($this->once())->method('setCity')->with('Updated City')->willReturn($currentSite);
        $currentSite->expects($this->once())->method('setCounty')->with('Updated County')->willReturn($currentSite);
        $currentSite->expects($this->once())->method('setCountry')->with($countryEntity)->willReturn($currentSite);
        $currentSite->expects($this->once())->method('setTelephone')->with('+44 20 9876 5432')->willReturn($currentSite);

        $this->entityManager->flush()->shouldBeCalled();

        $this->siteManager->updateSite($updatedSite);
    }

    public function testFetchSitesByOrganisationId(): void
    {
        $organisationId = 789;
        $sites = [
            $this->createMock(SiteEntity::class),
            $this->createMock(SiteEntity::class),
        ];
        
        $repository = $this->prophesize(SiteRepository::class);

        $this->entityManager->getRepository(SiteEntity::class)
            ->willReturn($repository->reveal());
        $repository->findByOrganisationId($organisationId)->willReturn($sites);

        $result = $this->siteManager->fetchSitesByOrganisationId($organisationId);

        $this->assertSame($sites, $result);
    }

    public function testFetchSitesByOrganisationIdReturnsEmptyArray(): void
    {
        $organisationId = 999;
        $repository = $this->prophesize(SiteRepository::class);

        $this->entityManager->getRepository(SiteEntity::class)
            ->willReturn($repository->reveal());
        $repository->findByOrganisationId($organisationId)->willReturn(null);

        $result = $this->siteManager->fetchSitesByOrganisationId($organisationId);

        $this->assertEquals([], $result);
    }

    public function testFetchSiteByUuid(): void
    {
        $uuid = 'site-uuid-123';
        $site = $this->createMock(SiteEntity::class);
        $repository = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(SiteEntity::class)
            ->willReturn($repository->reveal());
        $repository->findOneBy(['uuid' => $uuid])->willReturn($site);

        $result = $this->siteManager->fetchSiteByUuid($uuid);

        $this->assertSame($site, $result);
    }

    public function testFetchSiteByUuidReturnsNull(): void
    {
        $uuid = 'non-existent-uuid';
        $repository = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(SiteEntity::class)
            ->willReturn($repository->reveal());
        $repository->findOneBy(['uuid' => $uuid])->willReturn(null);

        $result = $this->siteManager->fetchSiteByUuid($uuid);

        $this->assertNull($result);
    }

    public function testFetchSiteById(): void
    {
        $siteId = 101;
        $site = $this->createMock(SiteEntity::class);
        $repository = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(SiteEntity::class)
            ->willReturn($repository->reveal());
        $repository->find($siteId)->willReturn($site);

        $result = $this->siteManager->fetchSiteById($siteId);

        $this->assertSame($site, $result);
    }

    public function testFetchSiteByIdReturnsNull(): void
    {
        $siteId = 404;
        $repository = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(SiteEntity::class)
            ->willReturn($repository->reveal());
        $repository->find($siteId)->willReturn(null);

        $result = $this->siteManager->fetchSiteById($siteId);

        $this->assertNull($result);
    }

    public function testDeleteSite(): void
    {
        $site = $this->createMock(SiteEntity::class);

        $this->entityManager->remove($site)->shouldBeCalled();
        $this->entityManager->flush()->shouldBeCalled();

        $this->siteManager->deleteSite($site);
    }

    public function testUpdateSiteWithNullValues(): void
    {
        // Test updating site with null values for nullable fields
        $updatedSite = $this->createMock(SiteEntity::class);
        $updatedSite->method('getId')->willReturn(456);
        $updatedSite->method('getName')->willReturn('Site Name');
        $updatedSite->method('getStreetAddress')->willReturn('123 Street');
        $updatedSite->method('getStreetAddress2')->willReturn(null);
        $updatedSite->method('getTown')->willReturn(null);
        $updatedSite->method('getCity')->willReturn('City');
        $updatedSite->method('getCounty')->willReturn(null);
        $updatedSite->method('getCountry')->willReturn($this->createMock(CountryEntity::class));
        $updatedSite->method('getTelephone')->willReturn('123456789');

        $currentSite = $this->createMock(SiteEntity::class);
        $repository = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(SiteEntity::class)
            ->willReturn($repository->reveal());
        $repository->find(456)->willReturn($currentSite);

        // Should handle null values properly
        $currentSite->expects($this->once())->method('setStreetAddress2')->with(null)->willReturn($currentSite);
        $currentSite->expects($this->once())->method('setTown')->with(null)->willReturn($currentSite);
        $currentSite->expects($this->once())->method('setCounty')->with(null)->willReturn($currentSite);
        
        // Non-null values should be set normally
        $currentSite->expects($this->once())->method('setName')->with('Site Name')->willReturn($currentSite);
        $currentSite->expects($this->once())->method('setStreetAddress')->with('123 Street')->willReturn($currentSite);
        $currentSite->expects($this->once())->method('setCity')->with('City')->willReturn($currentSite);
        $currentSite->expects($this->once())->method('setCountry')->with($this->isInstanceOf(CountryEntity::class))->willReturn($currentSite);
        $currentSite->expects($this->once())->method('setTelephone')->with('123456789')->willReturn($currentSite);

        $this->entityManager->flush()->shouldBeCalled();

        $this->siteManager->updateSite($updatedSite);
    }

    public function testUpdateSiteIncludesPostalCode(): void
    {
        // Test that updateSite method now includes postal code after fix
        $updatedSite = $this->createMock(SiteEntity::class);
        $updatedSite->method('getId')->willReturn(456);
        $updatedSite->method('getName')->willReturn('Test Site');
        $updatedSite->method('getStreetAddress')->willReturn('Test Street');
        $updatedSite->method('getStreetAddress2')->willReturn(null);
        $updatedSite->method('getTown')->willReturn(null);
        $updatedSite->method('getCity')->willReturn('Test City');
        $updatedSite->method('getCounty')->willReturn(null);
        $updatedSite->method('getCountry')->willReturn($this->createMock(CountryEntity::class));
        $updatedSite->method('getPostalCode')->willReturn('T1 2ST');
        $updatedSite->method('getTelephone')->willReturn('123456789');

        $currentSite = $this->createMock(SiteEntity::class);
        $repository = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(SiteEntity::class)
            ->willReturn($repository->reveal());
        $repository->find(456)->willReturn($currentSite);

        // setPostalCode should now be called after fix
        $currentSite->expects($this->once())->method('setPostalCode')->with('T1 2ST')->willReturn($currentSite);
        
        // All other setters should be called
        $currentSite->expects($this->once())->method('setName')->willReturn($currentSite);
        $currentSite->expects($this->once())->method('setStreetAddress')->willReturn($currentSite);
        $currentSite->expects($this->once())->method('setStreetAddress2')->willReturn($currentSite);
        $currentSite->expects($this->once())->method('setTown')->willReturn($currentSite);
        $currentSite->expects($this->once())->method('setCity')->willReturn($currentSite);
        $currentSite->expects($this->once())->method('setCounty')->willReturn($currentSite);
        $currentSite->expects($this->once())->method('setCountry')->willReturn($currentSite);
        $currentSite->expects($this->once())->method('setTelephone')->willReturn($currentSite);

        $this->entityManager->flush()->shouldBeCalled();

        $this->siteManager->updateSite($updatedSite);
    }
}