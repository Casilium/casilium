<?php

declare(strict_types=1);

namespace App\View\Helper;

use Carbon\Carbon;
use Carbon\CarbonInterface;
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
     * @param mixed $date date to parse
     * @param string|null $format date format
     * @return CarbonInterface carbon instance of date
     */
    public function __invoke($date = null, $format = null): string
    {
        if ($date === null) {
            $date = Carbon::now($this->timezone);
        } else {
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $date);
            $date->setTimezone($this->timezone);
        }

        if ($format === null) {
            return $date->format($this->format);
        }

        return $date->format($format);
    }
}
