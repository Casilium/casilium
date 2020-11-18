<?php

declare(strict_types=1);

namespace OrganisationSite\Hydrator;

use Laminas\Hydrator\AbstractHydrator;
use OrganisationSite\Entity\SiteEntity;
use OrganisationSite\Service\SiteManager;
use Ramsey\Uuid\Uuid;

class SiteEntityHydrator extends AbstractHydrator
{
    /** @var SiteManager */
    protected $siteManager;

    /**
     * @param SiteManager $siteManager Site manager instance to inject
     */
    public function __construct(SiteManager $siteManager)
    {
        $this->siteManager = $siteManager;
    }

    /**
     * Hydrate SiteEntity object with array data
     *
     * @param array $data
     */
    public function hydrate(array $data, object $object): object
    {
        if (! $object instanceof SiteEntity) {
            return $object;
        }

        if ($id = $data['id'] ?? null) {
            $object->setId((int) $id);
        }

        if (($uuid = $data['uuid'] ?? null) && (Uuid::isValid($data['uuid']))) {
            $object->setUuid(Uuid::fromString($uuid));
        }

        if ($name = $data['name'] ?? null) {
            $object->setName($name);
        }

        if ($county = $data['county'] ?? null) {
            $object->setCounty($county);
        }

        if ($streetAddress = $data['street_address'] ?? null) {
            $object->setStreetAddress($streetAddress);
        }

        if ($streetAddress2 = $data['street_address2'] ?? null) {
            $object->setStreetAddress2($streetAddress2);
        }

        if ($town = $data['town'] ?? null) {
            $object->setTown($town);
        }

        if ($city = $data['city'] ?? null) {
            $object->setCity($city);
        }

        if ($county = $data['county'] ?? null) {
            $object->setCounty($county);
        }

        if ($postcode = $data['postal_code'] ?? null) {
            $object->setPostalCode($data['postal_code']);
        }

        if ($country_id = $data['country_id'] ?? null) {
            $country = $this->siteManager->getCountry($data['country_id']);
            if ($country !== null) {
                $object->setCountry($country);
            }
        }

        if ($telephone = $data['telephone'] ?? null) {
            $object->setTelephone($telephone);
        }

        return $object;
    }

    /**
     * Return array representation of SiteEntity
     *
     * @param SiteEntity|object $object
     * @return array
     */
    public function extract(object $object): array
    {
        return $object->getArrayCopy();
    }
}
