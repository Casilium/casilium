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
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Organisation\Entity\Organisation")
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="id")
     *
     * @var Organisation
     */
    private $organisation;

    /**
     * @ORM\ManyToOne(targetEntity="OrganisationSite\Entity\SiteEntity")
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id")
     *
     * @var SiteEntity
     */
    private $site;

    /**
     * @ORM\Column(name="first_name", type="string", length=32)
     *
     * @var string
     */
    private $first_name;

    /**
     * @ORM\Column(name="middle_name", type="string", length=32)
     *
     * @var string
     */
    private $middle_name;

    /**
     * @ORM\Column(name="last_name", type="string", length=32)
     *
     * @var string
     */
    private $last_name;

    /**
     * @ORM\Column(name="work_telephone", type="string", length=32)
     *
     * @var string
     */
    private $work_telephone;

    /**
     * @ORM\Column(name="work_extension", type="string", length=20)
     *
     * @var string
     */
    private $work_extension;

    /**
     * @ORM\Column(name="mobile_telephone", type="string", length=20)
     *
     * @var string
     */
    private $mobile_telephone;

    /**
     * @ORM\Column(name="home_telephone", type="string", length=20)
     *
     * @var string
     */
    private $home_telephone;

    /**
     * @ORM\Column(name="work_email", type="string", length=20)
     *
     * @var string
     */
    private $work_email;

    /**
     * @ORM\Column(name="other_email", type="string", length=20)
     *
     * @var string
     */
    private $other_email;

    /**
     * @ORM\Column(name="gender", type="string", length=1)
     *
     * @var string
     */
    private $gender;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Contact
    {
        $this->id = $id;
        return $this;
    }

    public function getOrganisation(): Organisation
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
        return $this->first_name;
    }

    public function setFirstName(string $first_name): Contact
    {
        $this->first_name = $first_name;
        return $this;
    }

    public function getMiddleName(): string
    {
        return $this->middle_name;
    }

    public function setMiddleName(string $middle_name): Contact
    {
        $this->middle_name = $middle_name;
        return $this;
    }

    public function getLastName(): string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): Contact
    {
        $this->last_name = $last_name;
        return $this;
    }

    public function getWorkTelephone(): string
    {
        return $this->work_telephone;
    }

    public function setWorkTelephone(string $work_telephone): Contact
    {
        $this->work_telephone = $work_telephone;
        return $this;
    }

    public function getWorkExtension(): string
    {
        return $this->work_extension;
    }

    public function setWorkExtension(string $work_extension): Contact
    {
        $this->work_extension = $work_extension;
        return $this;
    }

    public function getMobileTelephone(): string
    {
        return $this->mobile_telephone;
    }

    public function setMobileTelephone(string $mobile_telephone): Contact
    {
        $this->mobile_telephone = $mobile_telephone;
        return $this;
    }

    public function getHomeTelephone(): string
    {
        return $this->home_telephone;
    }

    public function setHomeTelephone(string $home_telephone): Contact
    {
        $this->home_telephone = $home_telephone;
        return $this;
    }

    public function getWorkEmail(): string
    {
        return $this->work_email;
    }

    public function setWorkEmail(string $work_email): Contact
    {
        $this->work_email = $work_email;
        return $this;
    }

    public function getOtherEmail(): string
    {
        return $this->other_email;
    }

    public function setOtherEmail(string $other_email): Contact
    {
        $this->other_email = $other_email;
        return $this;
    }

    public function getGender(): string
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
        $this->id               = (int) $data['id'] ?? null;
        $this->organisation     = $data['organisation'] ?? null;
        $this->site             = $data['site'] ?? null;
        $this->first_name       = $data['first_name'] ?? null;
        $this->middle_name      = $data['middle_name'] ?? null;
        $this->last_name        = $data['last_name'] ?? null;
        $this->work_telephone   = $data['work_telephone'] ?? null;
        $this->work_extension   = $data['work_extension'] ?? null;
        $this->mobile_telephone = $data['mobile_telephone'] ?? null;
        $this->home_telephone   = $data['home_telephone'] ?? null;
        $this->work_email       = $data['work_email'] ?? null;
        $this->other_email      = $data['other_email'] ?? null;
        $this->gender           = $data['gender'] ?? null;
        return $this;
    }
}
