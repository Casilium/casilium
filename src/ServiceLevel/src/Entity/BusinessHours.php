<?php

declare(strict_types=1);

namespace ServiceLevel\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'business_hours')]
class BusinessHours
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', name: 'id', unique: true)]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected ?int $id;

    #[ORM\Column(type: 'string', name: 'name', nullable: false)]
    protected string $name;

    #[ORM\Column(type: 'string', name: 'timezone', nullable: false)]
    protected string $timezone;

    #[ORM\Column(type: 'string', name: 'mon_start', nullable: true)]
    protected ?string $monStart;

    #[ORM\Column(type: 'string', name: 'mon_end', nullable: true)]
    protected ?string $monEnd;

    #[ORM\Column(type: 'string', name: 'tue_start', nullable: true)]
    protected ?string $tueStart;

    #[ORM\Column(type: 'string', name: 'tue_end', nullable: true)]
    protected ?string $tueEnd;

    #[ORM\Column(type: 'string', name: 'wed_start', nullable: true)]
    protected ?string $wedStart;

    #[ORM\Column(type: 'string', name: 'wed_end', nullable: true)]
    protected ?string $wedEnd;

    #[ORM\Column(type: 'string', name: 'thu_start', nullable: true)]
    protected ?string $thuStart;

    #[ORM\Column(type: 'string', name: 'thu_end', nullable: true)]
    protected ?string $thuEnd;

    #[ORM\Column(type: 'string', name: 'fri_start', nullable: true)]
    protected ?string $friStart;

    #[ORM\Column(type: 'string', name: 'fri_end', nullable: true)]
    protected ?string $friEnd;

    #[ORM\Column(type: 'string', name: 'sat_start', nullable: true)]
    protected ?string $satStart;

    #[ORM\Column(type: 'string', name: 'sat_end', nullable: true)]
    protected ?string $satEnd;

    #[ORM\Column(type: 'string', name: 'sun_start', nullable: true)]
    protected ?string $sunStart;

    #[ORM\Column(type: 'string', name: 'sun_end', nullable: true)]
    protected ?string $sunEnd;

    #[ORM\Column(type: 'integer', name: 'mon_active', nullable: false)]
    protected bool $monActive;

    #[ORM\Column(type: 'integer', name: 'tue_active', nullable: false)]
    protected bool $tueActive;

    #[ORM\Column(type: 'integer', name: 'wed_active', nullable: false)]
    protected bool $wedActive;

    #[ORM\Column(type: 'integer', name: 'thu_active', nullable: false)]
    protected bool $thuActive;

    #[ORM\Column(type: 'integer', name: 'fri_active', nullable: false)]
    protected bool $friActive;

    #[ORM\Column(type: 'integer', name: 'sat_active', nullable: false)]
    protected bool $satActive;

    #[ORM\Column(type: 'integer', name: 'sun_active', nullable: false)]
    protected bool $sunActive;

    public function __construct()
    {
        // define default timezone and active days
        $this->setTimezone('Europe/London')
             ->setMonActive(true)
             ->setTueActive(true)
             ->setWedActive(true)
             ->setThuActive(true)
             ->setFriActive(true);

        $this->setMonStart('09:00')->setMonEnd('17:00');
        $this->setTueStart('09:00')->setTueEnd('17:00');
        $this->setWedStart('09:00')->setWedEnd('17:00');
        $this->setThuStart('09:00')->setThuEnd('17:00');
        $this->setFriStart('09:00')->setFriEnd('17:00');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): BusinessHours
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): BusinessHours
    {
        $this->name = $name;
        return $this;
    }

    public function getTimezone(): string
    {
        return $this->timezone;
    }

    public function setTimezone(string $timezone): BusinessHours
    {
        $this->timezone = $timezone;
        return $this;
    }

    public function getMonStart(): ?string
    {
        return $this->monStart;
    }

    public function setMonStart(?string $monStart): BusinessHours
    {
        $this->monStart = $monStart;
        return $this;
    }

    public function getMonEnd(): ?string
    {
        return $this->monEnd;
    }

    public function setMonEnd(?string $monEnd): BusinessHours
    {
        $this->monEnd = $monEnd;
        return $this;
    }

    public function getTueStart(): ?string
    {
        return $this->tueStart;
    }

    public function setTueStart(?string $tueStart): BusinessHours
    {
        $this->tueStart = $tueStart;
        return $this;
    }

    public function getTueEnd(): ?string
    {
        return $this->tueEnd;
    }

    public function setTueEnd(?string $tueEnd): BusinessHours
    {
        $this->tueEnd = $tueEnd;
        return $this;
    }

    public function getWedStart(): ?string
    {
        return $this->wedStart;
    }

    public function setWedStart(?string $wedStart): BusinessHours
    {
        $this->wedStart = $wedStart;
        return $this;
    }

    public function getWedEnd(): ?string
    {
        return $this->wedEnd;
    }

    public function setWedEnd(?string $wedEnd): BusinessHours
    {
        $this->wedEnd = $wedEnd;
        return $this;
    }

    public function getThuStart(): ?string
    {
        return $this->thuStart;
    }

    public function setThuStart(?string $thuStart): BusinessHours
    {
        $this->thuStart = $thuStart;
        return $this;
    }

    public function getThuEnd(): ?string
    {
        return $this->thuEnd;
    }

    public function setThuEnd(?string $thuEnd): BusinessHours
    {
        $this->thuEnd = $thuEnd;
        return $this;
    }

    public function getFriStart(): ?string
    {
        return $this->friStart;
    }

    public function setFriStart(?string $friStart): BusinessHours
    {
        $this->friStart = $friStart;
        return $this;
    }

    public function getFriEnd(): ?string
    {
        return $this->friEnd;
    }

    public function setFriEnd(?string $friEnd): BusinessHours
    {
        $this->friEnd = $friEnd;
        return $this;
    }

    public function getSatStart(): ?string
    {
        return $this->satStart;
    }

    public function setSatStart(?string $satStart): BusinessHours
    {
        $this->satStart = $satStart;
        return $this;
    }

    public function getSatEnd(): ?string
    {
        return $this->satEnd;
    }

    public function setSatEnd(?string $satEnd): BusinessHours
    {
        $this->satEnd = $satEnd;
        return $this;
    }

    public function getSunStart(): ?string
    {
        return $this->sunStart;
    }

    public function setSunStart(?string $sunStart): BusinessHours
    {
        $this->sunStart = $sunStart;
        return $this;
    }

    public function getSunEnd(): ?string
    {
        return $this->sunEnd;
    }

    public function setSunEnd(?string $sunEnd): BusinessHours
    {
        $this->sunEnd = $sunEnd;
        return $this;
    }

    public function getMonActive(): ?bool
    {
        return $this->monActive;
    }

    public function setMonActive(?bool $monActive): BusinessHours
    {
        $this->monActive = $monActive;
        return $this;
    }

    public function getTueActive(): ?bool
    {
        return $this->tueActive;
    }

    public function setTueActive(?bool $tueActive): BusinessHours
    {
        $this->tueActive = $tueActive;
        return $this;
    }

    public function getWedActive(): ?bool
    {
        return $this->wedActive;
    }

    public function setWedActive(?bool $wedActive): BusinessHours
    {
        $this->wedActive = $wedActive;
        return $this;
    }

    public function getThuActive(): ?bool
    {
        return $this->thuActive;
    }

    public function setThuActive(?bool $thuActive): BusinessHours
    {
        $this->thuActive = $thuActive;
        return $this;
    }

    public function getFriActive(): ?bool
    {
        return $this->friActive;
    }

    public function setFriActive(?bool $friActive): BusinessHours
    {
        $this->friActive = $friActive;
        return $this;
    }

    public function getSatActive(): ?bool
    {
        return $this->satActive;
    }

    public function setSatActive(?bool $satActive): BusinessHours
    {
        $this->satActive = $satActive;
        return $this;
    }

    public function getSunActive(): ?bool
    {
        return $this->sunActive;
    }

    public function setSunActive(?bool $sunActive): BusinessHours
    {
        $this->sunActive = $sunActive;
        return $this;
    }

    public function exchangeArray(array $data): void
    {
        $this->id       = $data['id'] ?? null;
        $this->name     = $data['name'];
        $this->timezone = $data['timezone'];
        $this->monStart = $data['mon_start'] ?? null;
        $this->monEnd   = $data['mon_end'] ?? null;
        $this->tueStart = $data['tue_start'] ?? null;
        $this->tueEnd   = $data['tue_end'] ?? null;
        $this->wedStart = $data['wed_start'] ?? null;
        $this->wedEnd   = $data['wed_end'] ?? null;
        $this->thuStart = $data['thu_start'] ?? null;
        $this->thuEnd   = $data['thu_end'] ?? null;
        $this->friStart = $data['fri_start'] ?? null;
        $this->friEnd   = $data['fri_end'] ?? null;
        $this->satStart = $data['sat_start'] ?? null;
        $this->satEnd   = $data['sat_end'] ?? null;
        $this->sunStart = $data['sun_start'] ?? null;
        $this->sunEnd   = $data['sun_end'] ?? null;

        $this->monActive = $data['mon_active'] ?? null;
        $this->tueActive = $data['tue_active'] ?? null;
        $this->wedActive = $data['wed_active'] ?? null;
        $this->thuActive = $data['thu_active'] ?? null;
        $this->friActive = $data['fri_active'] ?? null;
        $this->satActive = $data['sat_active'] ?? null;
        $this->sunActive = $data['sun_active'] ?? null;
    }

    public function getArrayCopy(): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'timezone'   => $this->timezone,
            'mon_start'  => $this->monStart,
            'mon_end'    => $this->monEnd,
            'tue_start'  => $this->tueStart,
            'tue_end'    => $this->tueEnd,
            'wed_start'  => $this->wedStart,
            'wed_end'    => $this->wedEnd,
            'thu_start'  => $this->thuStart,
            'thu_end'    => $this->thuEnd,
            'fri_start'  => $this->friStart,
            'fri_end'    => $this->friEnd,
            'sat_start'  => $this->satStart,
            'sat_end'    => $this->satEnd,
            'sun_start'  => $this->sunStart,
            'sun_end'    => $this->sunEnd,
            'mon_active' => $this->monActive,
            'tue_active' => $this->tueActive,
            'wed_active' => $this->wedActive,
            'thu_active' => $this->thuActive,
            'fri_active' => $this->friActive,
            'sat_active' => $this->satActive,
            'sun_active' => $this->sunActive,
        ];
    }
}
