<?php
declare(strict_types=1);

namespace OrganisationSite\Entity;

use Doctrine\ORM\Mapping as ORM;
use Organisation\Entity\Organisation;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Laminas\InputFilter\Factory as InputFilterFactory;

use Laminas\Filter;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator;

/**
 * @ORM\Entity(repositoryClass="\OrganisationSite\Repository\SiteRepository")
 * @ORM\Table(name="organisation_site")
 */
class SiteEntity
{
    /**
     * Internal Site ID
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="uuid", unique=true)
     * @var uuid
     */
    protected $uuid;

    /**
     * Site name or identifier
     * @ORM\Column(name="name", type="string", length=64)
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(name="street_address", type="string", length=64)
     * @var string
     */
    protected $street_address;

    /**
     * @ORM\Column(name="street_address2", type="string", length=64)
     * @var string
     */
    protected $street_address2;

    /**
     * @ORM\Column(name="town", type="string", length=64)
     * @var string
     */
    protected $town;


    /**
     * @ORM\Column(name="city", type="string", length=64)
     * @var string
     */
    protected $city;

    /**
     * County, Province or State
     * @ORM\Column(name="county", type="string", length=64)
     * @var string
     */
    protected $county;

    /**
     * @ORM\Column(name="postal_code", type="string", length=10)
     * @var string
     */
    protected $postal_code;

    /**
     * @ORM\Column(name="telephone", type="string", length=20)
     * @var string
     */
    protected $telephone;

    /**
     * Country
     * @ORM\OneToOne(targetEntity="OrganisationSite\Entity\CountryEntity", fetch="EAGER")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=false)
     * @var CountryEntity
     */
    protected $country;


    /**
     * @ORM\OneToOne(targetEntity="Organisation\Entity\Organisation", fetch="EAGER")
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="id", nullable=false)
     * @var Organisation
     */
    protected $organisation;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return SiteEntity
     */
    public function setId(int $id): SiteEntity
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return UuidInterface
     */
    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    /**
     * @param UuidInterface $uuid
     * @return SiteEntity
     */
    public function setUuid(UuidInterface $uuid): SiteEntity
    {
        $this->uuid = $uuid;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return SiteEntity
     */
    public function setName(string $name): SiteEntity
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return CountryEntity
     */
    public function getCountry(): CountryEntity
    {
        return $this->country;
    }

    /**
     * @param CountryEntity $country
     * @return SiteEntity
     */
    public function setCountry(CountryEntity $country): SiteEntity
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @param string $county
     * @return SiteEntity
     */
    public function setCounty(string $county): SiteEntity
    {
        $this->county = $county;
        return $this;
    }


    /**
     * @return string
     */
    public function getCounty(): string
    {
        return $this->county;
    }

    /**
     * @param string $county
     * @return SiteEntity
     */
    public function setRegion(string $county): SiteEntity
    {
        $this->county = $county;
        return $this;
    }

    /**
     * @return string
     */
    public function getStreetAddress(): string
    {
        return $this->street_address;
    }

    /**
     * @param string $street_address
     * @return SiteEntity
     */
    public function setStreetAddress(string $street_address): SiteEntity
    {
        $this->street_address = $street_address;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStreetAddress2(): ?string
    {
        return $this->street_address2;
    }

    /**
     * @param string $street_address2
     * @return SiteEntity
     */
    public function setStreetAddress2(string $street_address2): SiteEntity
    {
        $this->street_address2 = $street_address2;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTown(): ?string
    {
        return $this->town;
    }

    /**
     * @param string $town
     * @return SiteEntity|null
     */
    public function setTown(string $town): ?SiteEntity
    {
        $this->town = $town;
        return $this;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     * @return SiteEntity
     */
    public function setCity(string $city): SiteEntity
    {
        $this->city = $city;
        return $this;
    }



    /**
     * @return string
     */
    public function getPostalCode(): string
    {
        return $this->postal_code;
    }

    /**
     * @param string $postal_code
     * @return SiteEntity
     */
    public function setPostalCode(string $postal_code): SiteEntity
    {
        $this->postal_code = $postal_code;
        return $this;
    }

    /**
     * @return string
     */
    public function getTelephone(): string
    {
        return $this->telephone;
    }

    /**
     * @param string $telephone
     * @return SiteEntity
     */
    public function setTelephone(string $telephone): SiteEntity
    {
        $this->telephone = $telephone;
        return $this;
    }

    /**
     * @return Organisation
     */
    public function getOrganisation(): ?Organisation
    {
        return $this->organisation;
    }

    /**
     * @param Organisation|null $organisation
     * @return SiteEntity
     */
    public function setOrganisation(?Organisation $organisation): SiteEntity
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
    public function getArrayCopy() : array
    {
        //return get_object_vars($this);
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'street_address' => $this->street_address,
            'street_address2' => $this->street_address2,
            'town' => $this->town,
            'city' => $this->city,
            'county' => $this->county,
            'postal_code' => $this->postal_code,
            'telephone' => $this->telephone,
            'country' => $this->county,
            'organisation' => $this->organisation,
         ];
    }


    public function setValues(array $data) : SiteEntity
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

    public function getInputFilterSpecification() : InputFilterInterface
    {
        $factory = new InputFilterFactory();

        return $factory->createInputFilter([
            'name' => [
                'required' => true,
                'filters' => [
                    ['name' => Filter\StringTrim::class],
                    ['name' => Filter\StripTags::class],
                ],
            ],
            'street_address' => [
                'required' => true,
                'filters' => [
                    ['name' => Filter\StringTrim::class],
                    ['name' => Filter\StripTags::class],
                ],
            ],
            'street_address2' => [
                'required' => false,
                'filters' => [
                    ['name' => Filter\StringTrim::class],
                    ['name' => Filter\StripTags::class],
                ],
            ],
            'town' => [
                'required' => false,
                'filters' => [
                    ['name' => Filter\StringTrim::class],
                    ['name' => Filter\StripTags::class],
                ],
            ],
            'city' => [
                'required' => true,
                'filters' => [
                    ['name' => Filter\StringTrim::class],
                    ['name' => Filter\StripTags::class],
                ],
            ],
            'postal_code' => [
                'required' => true,
                'filters' => [
                    ['name' => Filter\StringTrim::class],
                    ['name' => Filter\StripTags::class],
                ],
            ],
            'county' => [
                'required' => false,
                'filters' => [
                    ['name' => Filter\StringTrim::class],
                    ['name' => Filter\StripTags::class],
                ],
            ],
            'country_id' => [
                'required' => true,
                'validators' => [
                    ['name' => Validator\Digits::class]
                ],
            ],
            'telephone' => [
                'required' => false,
                'filters' => [
                    ['name' => Filter\StringTrim::class],
                    ['name' => Filter\StripTags::class],
                ],
            ],
        ]);
    }
}
