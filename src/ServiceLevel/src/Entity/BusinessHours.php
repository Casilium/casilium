<?php

declare(strict_types=1);

namespace ServiceLevel\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="business_hours")
 */
class BusinessHours
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id", unique=true)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var int|null
     */
    protected $id;

    /**
     * @ORM\Column(type="string", name="name", nullable=false)
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="string", name="timezone", nullable=false)
     *
     * @var string
     */
    protected $timezone;

    /**
     * @ORM\Column(type="string", name="mon_start", nullable=true)
     *
     * @var string|null
     */
    protected $monStart;

    /**
     * @ORM\Column(type="string", name="mon_end", nullable=true)
     *
     * @var string|null
     */
    protected $monEnd;

    /**
     * @ORM\Column(type="string", name="tue_start", nullable=true)
     *
     * @var string|null
     */
    protected $tueStart;

    /**
     * @ORM\Column(type="string", name="tue_end", nullable=true)
     *
     * @var string|null
     */
    protected $tueEnd;

    /**
     * @ORM\Column(type="string", name="wed_start", nullable=true)
     *
     * @var string|null
     */
    protected $wedStart;

    /**
     * @ORM\Column(type="string", name="wed_end", nullable=true)
     *
     * @var string|null
     */
    protected $wedEnd;

    /**
     * @ORM\Column(type="string", name="thu_start", nullable=true)
     *
     * @var string|null
     */
    protected $thuStart;

    /**
     * @ORM\Column(type="string", name="thu_end", nullable=true)
     *
     * @var string|null
     */
    protected $thuEnd;

    /**
     * @ORM\Column(type="string", name="fri_start", nullable=true)
     *
     * @var string|null
     */
    protected $friStart;

    /**
     * @ORM\Column(type="string", name="fri_end", nullable=true)
     *
     * @var string|null
     */
    protected $friEnd;

    /**
     * @ORM\Column(type="string", name="sat_start", nullable=true)
     *
     * @var string|null
     */
    protected $satStart;

    /**
     * @ORM\Column(type="string", name="sat_end", nullable=true)
     *
     * @var string|null
     */
    protected $satEnd;

    /**
     * @ORM\Column(type="string", name="sun_start", nullable=true)
     *
     * @var string|null
     */
    protected $sunStart;

    /**
     * @ORM\Column(type="string", name="sun_end", nullable=true)
     *
     * @var string|null
     */
    protected $sunEnd;

    /**
     * @ORM\Column(type="integer", name="mon_active", nullable=false)
     *
     * @var bool
     */
    protected $monActive;

    /**
     * @ORM\Column(type="integer", name="tue_active", nullable=false)
     *
     * @var bool
     */
    protected $tueActive;

    /**
     * @ORM\Column(type="integer", name="wed_active", nullable=false)
     *
     * @var bool
     */
    protected $wedActive;

    /**
     * @ORM\Column(type="integer", name="thu_active", nullable=false)
     *
     * @var bool
     */
    protected $thuActive;

    /**
     * @ORM\Column(type="integer", name="fri_active", nullable=false)
     *
     * @var bool
     */
    protected $friActive;

    /**
     * @ORM\Column(type="integer", name="sat_active", nullable=false)
     *
     * @var bool
     */
    protected $satActive;

    /**
     * @ORM\Column(type="integer", name="sun_active", nullable=false)
     *
     * @var bool
     */
    protected $sunActive;

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

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return BusinessHours
     */
    public function setId(?int $id): BusinessHours
    {
        $this->id = $id;
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
     * @return BusinessHours
     */
    public function setName(string $name): BusinessHours
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int
     */
    public function getTimezone(): string
    {
        return $this->timezone;
    }

    /**
     * @param int $timezone
     * @return BusinessHours
     */
    public function setTimezone(string $timezone): BusinessHours
    {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMonStart(): ?string
    {
        return $this->monStart;
    }

    /**
     * @param string|null $monStart
     * @return BusinessHours
     */
    public function setMonStart(?string $monStart): BusinessHours
    {
        $this->monStart = $monStart;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMonEnd(): ?string
    {
        return $this->monEnd;
    }

    /**
     * @param string|null $monEnd
     * @return BusinessHours
     */
    public function setMonEnd(?string $monEnd): BusinessHours
    {
        $this->monEnd = $monEnd;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTueStart(): ?string
    {
        return $this->tueStart;
    }

    /**
     * @param string|null $tueStart
     * @return BusinessHours
     */
    public function setTueStart(?string $tueStart): BusinessHours
    {
        $this->tueStart = $tueStart;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTueEnd(): ?string
    {
        return $this->tueEnd;
    }

    /**
     * @param string|null $tueEnd
     * @return BusinessHours
     */
    public function setTueEnd(?string $tueEnd): BusinessHours
    {
        $this->tueEnd = $tueEnd;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getWedStart(): ?string
    {
        return $this->wedStart;
    }

    /**
     * @param string|null $wedStart
     * @return BusinessHours
     */
    public function setWedStart(?string $wedStart): BusinessHours
    {
        $this->wedStart = $wedStart;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getWedEnd(): ?string
    {
        return $this->wedEnd;
    }

    /**
     * @param string|null $wedEnd
     * @return BusinessHours
     */
    public function setWedEnd(?string $wedEnd): BusinessHours
    {
        $this->wedEnd = $wedEnd;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getThuStart(): ?string
    {
        return $this->thuStart;
    }

    /**
     * @param string|null $thuStart
     * @return BusinessHours
     */
    public function setThuStart(?string $thuStart): BusinessHours
    {
        $this->thuStart = $thuStart;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getThuEnd(): ?string
    {
        return $this->thuEnd;
    }

    /**
     * @param string|null $thuEnd
     * @return BusinessHours
     */
    public function setThuEnd(?string $thuEnd): BusinessHours
    {
        $this->thuEnd = $thuEnd;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFriStart(): ?string
    {
        return $this->friStart;
    }

    /**
     * @param string|null $friStart
     * @return BusinessHours
     */
    public function setFriStart(?string $friStart): BusinessHours
    {
        $this->friStart = $friStart;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFriEnd(): ?string
    {
        return $this->friEnd;
    }

    /**
     * @param string|null $friEnd
     * @return BusinessHours
     */
    public function setFriEnd(?string $friEnd): BusinessHours
    {
        $this->friEnd = $friEnd;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSatStart(): ?string
    {
        return $this->satStart;
    }

    /**
     * @param string|null $satStart
     * @return BusinessHours
     */
    public function setSatStart(?string $satStart): BusinessHours
    {
        $this->satStart = $satStart;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSatEnd(): ?string
    {
        return $this->satEnd;
    }

    /**
     * @param string|null $statEnd
     * @return BusinessHours
     */
    public function setSatEnd(?string $statEnd): BusinessHours
    {
        $this->statEnd = $statEnd;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSunStart(): ?string
    {
        return $this->sunStart;
    }

    /**
     * @param string|null $sunStart
     * @return BusinessHours
     */
    public function setSunStart(?string $sunStart): BusinessHours
    {
        $this->sunStart = $sunStart;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSunEnd(): ?string
    {
        return $this->sunEnd;
    }

    /**
     * @param string|null $sunEnd
     * @return BusinessHours
     */
    public function setSunEnd(?string $sunEnd): BusinessHours
    {
        $this->sunEnd = $sunEnd;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getMonActive(): ?bool
    {
        return $this->monActive;
    }

    /**
     * @param bool|null $monActive
     * @return BusinessHours
     */
    public function setMonActive(?bool $monActive): BusinessHours
    {
        $this->monActive = $monActive;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getTueActive(): ?bool
    {
        return $this->tueActive;
    }

    /**
     * @param bool|null $tueActive
     * @return BusinessHours
     */
    public function setTueActive(?bool $tueActive): BusinessHours
    {
        $this->tueActive = $tueActive;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getWedActive(): ?bool
    {
        return $this->wedActive;
    }

    /**
     * @param bool|null $wedActive
     * @return BusinessHours
     */
    public function setWedActive(?bool $wedActive): BusinessHours
    {
        $this->wedActive = $wedActive;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getThuActive(): ?bool
    {
        return $this->thuActive;
    }

    /**
     * @param bool|null $thuActive
     * @return BusinessHours
     */
    public function setThuActive(?bool $thuActive): BusinessHours
    {
        $this->thuActive = $thuActive;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getFriActive(): ?bool
    {
        return $this->friActive;
    }

    /**
     * @param bool|null $friActive
     * @return BusinessHours
     */
    public function setFriActive(?bool $friActive): BusinessHours
    {
        $this->friActive = $friActive;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getSatActive(): ?bool
    {
        return $this->satActive;
    }

    /**
     * @param bool|null $satActive
     * @return BusinessHours
     */
    public function setSatActive(?bool $satActive): BusinessHours
    {
        $this->satActive = $satActive;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getSunActive(): ?bool
    {
        return $this->sunActive;
    }

    /**
     * @param bool|null $sunActive
     * @return BusinessHours
     */
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
