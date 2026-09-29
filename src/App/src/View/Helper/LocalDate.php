<?php

declare(strict_types=1);

namespace App\View\Helper;

use Carbon\Carbon;
use DateTimeInterface;
use Laminas\View\Helper\AbstractHelper;

class LocalDate extends AbstractHelper
{
    /** @var string */
    protected $timezone;

    /** @var string */
    protected $format = 'd/m/Y H:i:s';

    public function __construct(string $timezone, ?string $format)
    {
        $this->timezone = $timezone;
        $this->format   = $format ?? 'd/m/Y H:i:s';
    }

    /**
     * Render a date in the display timezone.
     *
     * Only accepts a DateTimeInterface. A string carries no timezone, so
     * accepting one would mean guessing which zone it was written in.
     *
     * @param DateTimeInterface|null $date date to render, or null for now
     * @param string|null $format date format, defaults to the configured one
     */
    public function __invoke(?DateTimeInterface $date = null, ?string $format = null): string
    {
        $date = $date === null
            ? Carbon::now($this->timezone)
            : Carbon::instance($date)->setTimezone($this->timezone);

        return $date->format($format ?? $this->format);
    }
}
