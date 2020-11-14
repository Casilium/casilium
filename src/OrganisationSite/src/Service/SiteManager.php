<?php

declare(strict_types=1);

namespace OrganisationSite\Service;

use Doctrine\ORM\EntityManagerInterface;
use Laminas\Form\Annotation\Hydrator;
use Laminas\Hydrator\Aggregate\AggregateHydrator;
use Laminas\View\Resolver\AggregateResolver;
use Organisation\Entity\Organisation;
use OrganisationSite\Entity\CountryEntity;
use OrganisationSite\Entity\SiteEntity;
use OrganisationSite\Hydrator\SiteEntityHydrator;
use Ramsey\Uuid\Uuid;

class SiteManager
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * SiteManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Find an organisation by its uuid
     *
     * @param string $uuid
     * @return Organisation|Object|null
     */
    public function getOrganisationByUuid(string $uuid): ?Organisation
    {
        return $this->entityManager->getRepository(Organisation::class)
            ->findOneBy(['uuid' => $uuid]);
    }

    /**
     * Returns the list of countries
     *
     * @return array|null
     */
    public function getCountries(): ?array
    {
        $result =  $this->entityManager->getRepository(CountryEntity::class)
            ->findAll();

        $countries = [];
        foreach ($result as $country) {
            $countries[$country->getId()] = $country->getName();
        }
        return $countries;
    }

    /**
     * Fetch specific country
     *
     * @param int $id
     * @return CountryEntity|null
     */
    public function getCountry(int $id): ?CountryEntity
    {
        return $this->entityManager->getRepository(CountryEntity::class)->find($id);
    }

    /**
     * Insert site into database
     *
     * @param SiteEntity $site
     * @return int|null
     */
    public function createSite(SiteEntity $site): ?int
    {
        $site->setUuid(Uuid::uuid4());
        $this->entityManager->persist($site);
        $this->entityManager->flush();

        return $site->getId();
    }

    /**
     * Update site
     *
     * @param SiteEntity $entity
     */
    public function updateSite(SiteEntity $entity): void
    {
        /** @var SiteEntity $site */
        $site = $this->entityManager->getRepository(SiteEntity::class)->find($entity->getId());

        // copy data
        $site->setName($entity->getName());
        $site->setStreetAddress($entity->getStreetAddress());
        $site->setStreetAddress2($entity->getStreetAddress2());
        $site->setTown($entity->getTown());
        $site->setCity($entity->getCity());
        $site->setCounty($entity->getCounty());
        $site->setCountry($entity->getCountry());
        $site->setTelephone($entity->getTelephone());

        // save
        $this->entityManager->flush();
    }

    /**
     * Fetch organisation's sites from id
     *
     * @param int $id
     * @return array
     */
    public function fetchSitesByOrganisationId(int $id): ?array
    {
        return $this->entityManager->getRepository(SiteEntity::class)
            ->findByOrganisationId($id);
    }

    /**
     * Find site by uuid
     *
     * @param string $uuid
     * @return SiteEntity|Object|null
     */
    public function fetchSiteByUuid(string $uuid): ?SiteEntity
    {
        return $this->entityManager->getRepository(SiteEntity::class)
            ->findOneBy(['uuid' => $uuid]);
    }

    /**
     * Remove site from database
     *
     * @param SiteEntity $site
     */
    public function deleteSite(SiteEntity $site): void
    {
        $this->entityManager->remove($site);
        $this->entityManager->flush();
    }

}