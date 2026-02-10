<?php

declare(strict_types=1);

namespace OrganisationTest\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Organisation\Entity\Organisation;
use Organisation\Exception\OrganisationExistsException;
use Organisation\Exception\OrganisationSitesExistException;
use Organisation\Service\OrganisationManager;
use OrganisationSite\Entity\SiteEntity;
use OrganisationSite\Service\SiteManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class OrganisationManagerTest extends TestCase
{
    use ProphecyTrait;

    private OrganisationManager $organisationManager;
    private ObjectProphecy $entityManager;
    private ObjectProphecy $siteManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);
        $this->siteManager   = $this->prophesize(SiteManager::class);

        $this->organisationManager = new OrganisationManager(
            $this->entityManager->reveal(),
            $this->siteManager->reveal()
        );
    }

    public function testAutoCompleteNameReturnsActiveOrganisations(): void
    {
        $expected = [
            ['value' => 1, 'text' => 'Acme Corp'],
            ['value' => 2, 'text' => 'Acme Ltd'],
        ];

        $query = $this->prophesize(Query::class);
        $query->getArrayResult()->willReturn($expected);

        $qb = $this->prophesize(QueryBuilder::class);
        $qb->select('o.id as value, o.name as text')->willReturn($qb->reveal());
        $qb->from(Organisation::class, 'o')->willReturn($qb->reveal());
        $qb->where('o.name LIKE :name')->willReturn($qb->reveal());
        $qb->andWhere('o.isActive = true')->willReturn($qb->reveal());
        $qb->orderBy('o.name')->willReturn($qb->reveal());
        $qb->setParameter('name', 'Acme%')->willReturn($qb->reveal());
        $qb->getQuery()->willReturn($query->reveal());

        $this->entityManager->createQueryBuilder()->willReturn($qb->reveal());

        $result = $this->organisationManager->autoCompleteName('Acme');

        $this->assertSame($expected, $result);
    }

    public function testAutoCompleteNameReturnsEmptyArray(): void
    {
        $query = $this->prophesize(Query::class);
        $query->getArrayResult()->willReturn([]);

        $qb = $this->prophesize(QueryBuilder::class);
        $qb->select('o.id as value, o.name as text')->willReturn($qb->reveal());
        $qb->from(Organisation::class, 'o')->willReturn($qb->reveal());
        $qb->where('o.name LIKE :name')->willReturn($qb->reveal());
        $qb->andWhere('o.isActive = true')->willReturn($qb->reveal());
        $qb->orderBy('o.name')->willReturn($qb->reveal());
        $qb->setParameter('name', 'NonExistent%')->willReturn($qb->reveal());
        $qb->getQuery()->willReturn($query->reveal());

        $this->entityManager->createQueryBuilder()->willReturn($qb->reveal());

        $result = $this->organisationManager->autoCompleteName('NonExistent');

        $this->assertSame([], $result);
    }

    public function testFindOrganisationById(): void
    {
        $organisation = $this->createMock(Organisation::class);
        $repository   = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(Organisation::class)
            ->willReturn($repository->reveal());
        $repository->findOneBy(['id' => 1])->willReturn($organisation);

        $result = $this->organisationManager->findOrganisationById(1);

        $this->assertSame($organisation, $result);
    }

    public function testFindOrganisationByIdReturnsNull(): void
    {
        $repository = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(Organisation::class)
            ->willReturn($repository->reveal());
        $repository->findOneBy(['id' => 999])->willReturn(null);

        $result = $this->organisationManager->findOrganisationById(999);

        $this->assertNull($result);
    }

    public function testFindOrganisationByUuid(): void
    {
        $uuid         = 'test-uuid-1234';
        $organisation = $this->createMock(Organisation::class);
        $repository   = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(Organisation::class)
            ->willReturn($repository->reveal());
        $repository->findOneBy(['uuid' => $uuid])->willReturn($organisation);

        $result = $this->organisationManager->findOrganisationByUuid($uuid);

        $this->assertSame($organisation, $result);
    }

    public function testFindOrganisationByUuidReturnsNull(): void
    {
        $uuid       = 'non-existent-uuid';
        $repository = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(Organisation::class)
            ->willReturn($repository->reveal());
        $repository->findOneBy(['uuid' => $uuid])->willReturn(null);

        $result = $this->organisationManager->findOrganisationByUuid($uuid);

        $this->assertNull($result);
    }

    public function testFindOrganisationByName(): void
    {
        $organisation = $this->createMock(Organisation::class);
        $repository   = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(Organisation::class)
            ->willReturn($repository->reveal());
        $repository->findOneBy(['name' => 'Acme Corp'])->willReturn($organisation);

        $result = $this->organisationManager->findOrganisationByName('Acme Corp');

        $this->assertSame($organisation, $result);
    }

    public function testFindOrganisationByNameReturnsNull(): void
    {
        $repository = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(Organisation::class)
            ->willReturn($repository->reveal());
        $repository->findOneBy(['name' => 'Unknown'])->willReturn(null);

        $result = $this->organisationManager->findOrganisationByName('Unknown');

        $this->assertNull($result);
    }

    public function testFetchAll(): void
    {
        $organisations = [
            $this->createMock(Organisation::class),
            $this->createMock(Organisation::class),
        ];

        $repository = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(Organisation::class)
            ->willReturn($repository->reveal());
        $repository->findAll()->willReturn($organisations);

        $result = $this->organisationManager->fetchAll();

        $this->assertSame($organisations, $result);
    }

    public function testDeleteOrganisation(): void
    {
        $organisation = $this->createMock(Organisation::class);
        $organisation->method('getId')->willReturn(1);

        $this->siteManager->fetchSitesByOrganisationId(1)->willReturn([]);

        $this->entityManager->remove($organisation)->shouldBeCalled();
        $this->entityManager->flush()->shouldBeCalled();

        $this->organisationManager->delete($organisation);
    }

    public function testDeleteOrganisationThrowsWhenSitesExist(): void
    {
        $organisation = $this->createMock(Organisation::class);
        $organisation->method('getId')->willReturn(1);
        $organisation->method('getName')->willReturn('Acme Corp');

        $this->siteManager->fetchSitesByOrganisationId(1)
            ->willReturn([$this->createMock(SiteEntity::class)]);

        $this->expectException(OrganisationSitesExistException::class);

        $this->organisationManager->delete($organisation);
    }

    public function testCreateOrganisationThrowsWhenDuplicate(): void
    {
        $existing = $this->createMock(Organisation::class);
        $existing->method('getName')->willReturn('Acme Corp');

        $organisation = $this->createMock(Organisation::class);
        $organisation->method('getName')->willReturn('Acme Corp');

        $repository = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(Organisation::class)
            ->willReturn($repository->reveal());
        $repository->findOneBy(['name' => 'Acme Corp'])->willReturn($existing);

        $this->expectException(OrganisationExistsException::class);

        $this->organisationManager->createOrganisation($organisation);
    }

    public function testCreateOrganisationPersists(): void
    {
        $organisation = $this->createMock(Organisation::class);
        $organisation->method('getName')->willReturn('New Corp');

        $repository = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(Organisation::class)
            ->willReturn($repository->reveal());
        $repository->findOneBy(['name' => 'New Corp'])->willReturn(null);

        $this->entityManager->persist($organisation)->shouldBeCalled();
        $this->entityManager->flush()->shouldBeCalled();

        $result = $this->organisationManager->createOrganisation($organisation);

        $this->assertSame($organisation, $result);
    }
}
