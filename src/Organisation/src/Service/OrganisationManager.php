<?php

declare(strict_types=1);

namespace Organisation\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Organisation\Entity\Domain;
use Organisation\Entity\Organisation;
use Organisation\Entity\OrganisationInterface;
use Organisation\Exception\OrganisationExistsException;
use Organisation\Exception\OrganisationSitesExistException;
use Organisation\Hydrator\OrganisationHydrator;
use OrganisationSite\Service\SiteManager;
use function in_array;

class OrganisationManager
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var SiteManager */
    protected $siteManager;

    public function __construct(EntityManagerInterface $entityManager, SiteManager $siteManager)
    {
        $this->entityManager = $entityManager;
        $this->siteManager   = $siteManager;
    }

    /**
     * Create new organisation
     *
     * @param OrganisationInterface $organisation organisation object to save
     * @return OrganisationInterface|null saved organisation entity
     */
    public function createOrganisation(OrganisationInterface $organisation): ?OrganisationInterface
    {
        // if organisation exists, throw an exception
        if ($result = $this->findOrganisationByName($organisation->getName())) {
            throw OrganisationExistsException::whenCreating($result->getName());
        }

        // save the organisation
        $this->entityManager->persist($organisation);
        $this->entityManager->flush();

        // return the newly created organisationId
        return $organisation;
    }

    /**
     * @param array $data data to populate organisation
     * @return OrganisationInterface|null organisation or null
     */
    public function createOrganisationFromArray(array $data): ?OrganisationInterface
    {
        $hydrator = new OrganisationHydrator();

        /** @var Organisation $organisation */
        $organisation = $hydrator->hydrate($data, new Organisation());

        return $this->createOrganisation($organisation);
    }

    /**
     * @param string $name name of organisation to find
     * @return Organisation|null organisation or null
     */
    public function findOrganisationByName(string $name): ?Organisation
    {
        // find organisation in repository
        $organisation = $this->entityManager->getRepository(Organisation::class)
            ->findOneBy(['name' => $name]);

        // return organisation
        if ($organisation instanceof Organisation) {
            return $organisation;
        }

        // or return nothing
        return null;
    }

    /**
     * @param int $id id of organisation
     * @return Organisation|Object|null organisation or null if not found
     */
    public function findOrganisationById(int $id): ?Organisation
    {
        return $this->entityManager->getRepository(Organisation::class)
            ->findOneBy(['id' => $id]);
    }

    public function findOrganisationByUuid(string $uuid): ?Organisation
    {
        return $this->entityManager->getRepository(Organisation::class)
            ->findOneByUuid($uuid);
    }

    /**
     * Update Organisation
     *
     * Update existing organisation, first update organisation details then remove any domains no longer required
     * and finally add the new domains.
     *
     * @param int $id organisation to update
     * @param array $data data to populate
     * @throws Exception
     */
    public function updateOrganisation(int $id, array $data): void
    {
        // clear pending doctrine operations
        $this->entityManager->clear();

        /** @var Organisation $organisation */
        $organisation = $this->entityManager->getRepository(Organisation::class)->find($id);
        $organisation->setName($data['name']);
        $organisation->setIsActive($data['is_active']);

        $domains = [];
        foreach ($organisation->getDomains() as $domain) {
            // if domain is not in list then remove
            if (! in_array($domain->getName(), $data['domain'])) {
                // remove domain
                $organisation->removeDomain($domain);
            } else {
                // otherwise add to list of domains to keep
                $domains[] = $domain->getName();
            }
        }

        // loop through domains passed
        foreach ($data['domain'] as $domain) {
            // if domain passed is not in current list
            if (! in_array($domain, $domains)) {
                // it's a new one so we need to add it.
                $newDomain = new Domain();
                $newDomain->setName($domain);
                $newDomain->setOrganisation($organisation);
                $organisation->addDomain($newDomain);
            }
        }

        // update modification date and save
        $organisation->setModified();
        $this->entityManager->flush();
    }

    /**
     * Remove domains belonging to organisation
     *
     * @param int $id id of organisation
     */
    public function removeOrganisationDomains(int $id): void
    {
        $domains = $this->entityManager->getRepository(Domain::class)
            ->findBy(['organisation' => $id]);

        foreach ($domains as $domain) {
            $this->entityManager->remove($domain);
        }
    }

    /**
     * Fetch all organisations
     *
     * @return array
     */
    public function fetchAll(): array
    {
        return $this->entityManager->getRepository(Organisation::class)->findAll();
    }

    /**
     * Delete an organisation
     *
     * @param Organisation $organisation organisation to delete
     */
    public function delete(Organisation $organisation): void
    {
        // check if organisation has sites before deleting.
        if ($sites = $this->siteManager->fetchSitesByOrganisationId($organisation->getId())) {
            throw OrganisationSitesExistException::whenDeleting($organisation->getName());
        }

        // remove the organisation
        $this->entityManager->remove($organisation);
        $this->entityManager->flush();
    }

    /**
     * Used for input autocomplete to select organisation
     *
     * @param string $name search string
     * @return array list of matching organisations
     */
    public function autoCompleteName(string $name): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('o.id as value, o.name as text')
            ->from(Organisation::class, 'o')
            ->where('o.name LIKE :name')
            ->orderBy('o.name')
            ->setParameter('name', $name . '%')
            ->getQuery()
            ->getArrayResult();
    }
}
