<?php

declare(strict_types=1);

namespace OrganisationSite\Entity;

use Doctrine\ORM\Mapping as ORM;
use Laminas\Filter;
use Laminas\InputFilter\Factory as InputFilterFactory;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator;
use Organisation\Entity\Organisation;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

use function array_filter;
use function implode;
use function is_string;

/**
 * @ORM\Entity(repositoryClass="\OrganisationSite\Repository\SiteRepository")
 * @ORM\Table(name="organisation_site")
 */
class SiteEntity
{
    /**
     * Internal Site ID
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected int $id;

    /** @ORM\Column(type="uuid", unique=true) */
    protected UuidInterface $uuid;

    /**
     * Site name or identifier
     *
     * @ORM\Column(name="name", type="string", length=64)
     */
    protected ?string $name = null;

    /** @ORM\Column(name="street_address", type="string", length=64) */
    protected ?string $streetAddress = null;

    /** @ORM\Column(name="street_address2", type="string", length=64) */
    protected ?string $streetAddress2 = null;

    /** @ORM\Column(name="town", type="string", length=64) */
    protected ?string $town = null;

    /** @ORM\Column(name="city", type="string", length=64) */
    protected ?string $city = null;

    /**
     * County, Province or State
     *
     * @ORM\Column(name="county", type="string", length=64)
     */
    protected ?string $county = null;

    /** @ORM\Column(name="postal_code", type="string", length=10) */
    protected ?string $postalCode = null;

    /** @ORM\Column(name="telephone", type="string", length=20) */
    protected ?string $telephone = null;

    public function __construct()
    {
        $this->id   = 0;
        $this->uuid = Uuid::uuid4();
    }

    /**
     * Country
     *
     * @ORM\OneToOne(targetEntity="OrganisationSite\Entity\CountryEntity")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=false)
     */
    protected ?CountryEntity $country = null;

    /**
     * @ORM\OneToOne(targetEntity="Organisation\Entity\Organisation")
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="id", nullable=false, onDelete="cascade")
     */
    protected ?Organisation $organisation = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): SiteEntity
    {
        $this->id = $id;
        return $this;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function setUuid(UuidInterface $uuid): SiteEntity
    {
        $this->uuid = $uuid;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): SiteEntity
    {
        $this->name = $name;
        return $this;
    }

    public function getCountry(): ?CountryEntity
    {
        return $this->country;
    }

    public function setCountry(CountryEntity $country): SiteEntity
    {
        $this->country = $country;
        return $this;
    }

    public function setCounty(?string $county): SiteEntity
    {
        $this->county = $county;
        return $this;
    }

    public function getCounty(): ?string
    {
        return $this->county;
    }

    public function getStreetAddress(): ?string
    {
        return $this->streetAddress;
    }

    public function setStreetAddress(string $streetAddress): SiteEntity
    {
        $this->streetAddress = $streetAddress;
        return $this;
    }

    public function getStreetAddress2(): ?string
    {
        return $this->streetAddress2;
    }

    public function setStreetAddress2(?string $streetAddress2): SiteEntity
    {
        $this->streetAddress2 = $streetAddress2;
        return $this;
    }

    public function getTown(): ?string
    {
        return $this->town;
    }

    public function setTown(?string $town): ?SiteEntity
    {
        $this->town = $town;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): SiteEntity
    {
        $this->city = $city;
        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): SiteEntity
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): SiteEntity
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getOrganisation(): ?Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(Organisation $organisation): SiteEntity
    {
        $this->organisation = $organisation;
        return $this;
    }

    public function getAddressAsString(): string
    {
        $address[] = $this->getStreetAddress();
        $address[] = $this->getStreetAddress2();
        $address[] = $this->getTown();
        $address[] = $this->getCity();
        $address[] = $this->getCounty();
        $address[] = $this->getCountry()->getName();
        $address[] = $this->getPostalCode();
        return implode(', ', array_filter($address));
    }

    /**
     * @return array
     */
    public function getArrayCopy(): array
    {
        //return get_object_vars($this);
        return [
            'id'              => $this->id,
            'uuid'            => $this->uuid,
            'name'            => $this->name,
            'street_address'  => $this->streetAddress,
            'street_address2' => $this->streetAddress2,
            'town'            => $this->town,
            'city'            => $this->city,
            'county'          => $this->county,
            'postal_code'     => $this->postalCode,
            'telephone'       => $this->telephone,
            'country_id'      => $this->country?->getId(),
            'organisation'    => $this->organisation,
        ];
    }

    public function setValues(array $data): SiteEntity
    {
        if (! isset($this->id)) {
            $this->id = $data['id'] ?? null;
        }

        if (! isset($this->uuid)) {
            $this->uuid = Uuid::uuid4();
        } elseif (isset($data['uuid']) && is_string($data['uuid']) && Uuid::isValid($data['uuid'])) {
            $this->setUuid(Uuid::fromString($data['uuid']));
        }

        if (isset($data['name'])) {
            $this->name = $data['name'];
            $this->setName($data['name']);
        }

        if (isset($data['street_address'])) {
            $this->setStreetAddress($data['street_address']);
        }

        if (isset($data['street_address2'])) {
            $this->setStreetAddress2($data['street_address2']);
        }

        if (isset($data['town'])) {
            $this->setTown($data['town']);
        }

        if (isset($data['city'])) {
            $this->setCity($data['city']);
        }

        if (isset($data['county'])) {
            $this->setCounty($data['county']);
        }

        if (isset($data['country'])) {
            $this->setCountry($data['country']);
        }

        if (isset($data['postal_code'])) {
            $this->setPostalCode($data['postal_code']);
        }

        if (isset($data['telephone'])) {
            $this->setTelephone($data['telephone']);
        }

        return $this;
    }

    public function exchangeArray(array $data): void
    {
        $this->setValues($data);
    }

    public function getInputFilterSpecification(): InputFilterInterface
    {
        $factory = new InputFilterFactory();

        return $factory->createInputFilter([
            'name'            => [
                'required' => true,
                'filters'  => [
                    ['name' => Filter\StringTrim::class],
                    ['name' => Filter\StripTags::class],
                ],
            ],
            'street_address'  => [
                'required' => true,
                'filters'  => [
                    ['name' => Filter\StringTrim::class],
                    ['name' => Filter\StripTags::class],
                ],
            ],
            'street_address2' => [
                'required' => false,
                'filters'  => [
                    ['name' => Filter\StringTrim::class],
                    ['name' => Filter\StripTags::class],
                ],
            ],
            'town'            => [
                'required' => false,
                'filters'  => [
                    ['name' => Filter\StringTrim::class],
                    ['name' => Filter\StripTags::class],
                ],
            ],
            'city'            => [
                'required' => true,
                'filters'  => [
                    ['name' => Filter\StringTrim::class],
                    ['name' => Filter\StripTags::class],
                ],
            ],
            'postal_code'     => [
                'required' => true,
                'filters'  => [
                    ['name' => Filter\StringTrim::class],
                    ['name' => Filter\StripTags::class],
                ],
            ],
            'county'          => [
                'required' => false,
                'filters'  => [
                    ['name' => Filter\StringTrim::class],
                    ['name' => Filter\StripTags::class],
                ],
            ],
            'country_id'      => [
                'required'   => true,
                'validators' => [
                    ['name' => Validator\Digits::class],
                ],
            ],
            'telephone'       => [
                'required' => false,
                'filters'  => [
                    ['name' => Filter\StringTrim::class],
                    ['name' => Filter\StripTags::class],
                ],
            ],
        ]);
    }
}
