<?php

declare(strict_types=1);

namespace OrganisationContact\Entity;

use Doctrine\ORM\Mapping as ORM;
use Organisation\Entity\Organisation;
use OrganisationSite\Entity\SiteEntity;

use function get_object_vars;

/**
 * @ORM\Entity(repositoryClass="OrganisationContact\Repository\ContactRepository")
 * @ORM\Table(name="organisation_contact")
 */
class Contact
{
    /**
     * Internal Contact ID
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Organisation\Entity\Organisation")
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="id")
     */
    private ?Organisation $organisation = null;

    /**
     * @ORM\ManyToOne(targetEntity="OrganisationSite\Entity\SiteEntity")
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id")
     */
    private ?SiteEntity $site = null;

    /** @ORM\Column(name="first_name", type="string", length=32) */
    private string $firstName;

    /** @ORM\Column(name="middle_name", type="string", length=32) */
    private string $middleName;

    /** @ORM\Column(name="last_name", type="string", length=32) */
    private string $lastName;

    /** @ORM\Column(name="work_telephone", type="string", length=32) */
    private string $workTelephone;

    /** @ORM\Column(name="work_extension", type="string", length=20) */
    private string $workExtension;

    /** @ORM\Column(name="mobile_telephone", type="string", length=20) */
    private string $mobileTelephone;

    /** @ORM\Column(name="home_telephone", type="string", length=20) */
    private string $homeTelephone;

    /** @ORM\Column(name="work_email", type="string", length=20) */
    private string $workEmail;

    /** @ORM\Column(name="other_email", type="string", length=20) */
    private string $otherEmail;

    /** @ORM\Column(name="gender", type="string", length=1) */
    private ?string $gender = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Contact
    {
        $this->id = $id;
        return $this;
    }

    public function getOrganisation(): ?Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(Organisation $organisation): Contact
    {
        $this->organisation = $organisation;
        return $this;
    }

    public function getSite(): ?SiteEntity
    {
        return $this->site;
    }

    public function setSite(?SiteEntity $site): Contact
    {
        $this->site = $site;
        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): Contact
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getMiddleName(): string
    {
        return $this->middleName;
    }

    public function setMiddleName(string $middleName): Contact
    {
        $this->middleName = $middleName;
        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): Contact
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getWorkTelephone(): string
    {
        return $this->workTelephone;
    }

    public function setWorkTelephone(string $workTelephone): Contact
    {
        $this->workTelephone = $workTelephone;
        return $this;
    }

    public function getWorkExtension(): string
    {
        return $this->workExtension;
    }

    public function setWorkExtension(string $workExtension): Contact
    {
        $this->workExtension = $workExtension;
        return $this;
    }

    public function getMobileTelephone(): string
    {
        return $this->mobileTelephone;
    }

    public function setMobileTelephone(string $mobileTelephone): Contact
    {
        $this->mobileTelephone = $mobileTelephone;
        return $this;
    }

    public function getHomeTelephone(): string
    {
        return $this->homeTelephone;
    }

    public function setHomeTelephone(string $homeTelephone): Contact
    {
        $this->homeTelephone = $homeTelephone;
        return $this;
    }

    public function getWorkEmail(): string
    {
        return $this->workEmail;
    }

    public function setWorkEmail(string $workEmail): Contact
    {
        $this->workEmail = $workEmail;
        return $this;
    }

    public function getOtherEmail(): string
    {
        return $this->otherEmail;
    }

    public function setOtherEmail(string $otherEmail): Contact
    {
        $this->otherEmail = $otherEmail;
        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): Contact
    {
        $this->gender = $gender;
        return $this;
    }

    /**
     * @return array
     */
    public function getArrayCopy(): array
    {
        return get_object_vars($this);
    }

    /**
     * @param array $data
     */
    public function exchangeArray(array $data): Contact
    {
        $this->id              = (int) $data['id'] ?? null;
        $this->organisation    = $data['organisation'] ?? null;
        $this->site            = $data['site'] ?? null;
        $this->firstName       = $data['first_name'] ?? null;
        $this->middleName      = $data['middle_name'] ?? null;
        $this->lastName        = $data['last_name'] ?? null;
        $this->workTelephone   = $data['work_telephone'] ?? null;
        $this->workExtension   = $data['work_extension'] ?? null;
        $this->mobileTelephone = $data['mobile_telephone'] ?? null;
        $this->homeTelephone   = $data['home_telephone'] ?? null;
        $this->workEmail       = $data['work_email'] ?? null;
        $this->otherEmail      = $data['other_email'] ?? null;
        $this->gender          = $data['gender'] ?? null;
        return $this;
    }
}
