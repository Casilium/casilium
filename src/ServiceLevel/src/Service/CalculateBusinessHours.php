<?php

declare(strict_types=1);

namespace ServiceLevel\Service;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use ServiceLevel\Entity\BusinessHours;
use function array_keys;
use function array_map;
use function count;
use function explode;
use function is_array;
use function number_format;
use function preg_match;
use function sprintf;
use function strcmp;
use function strpos;
use function strtolower;

class CalculateBusinessHours
{
    /** @var array */
    private $options;

    /** @var BusinessHours */
    protected $businessHours;

    /** @var array */
    protected $workingHours;

    /**
     * @param array $options
     */
    public function __construct(BusinessHours $businessHours, array $options = [])
    {
        $this->options       = $options;
        $this->businessHours = $businessHours;
        $this->workingHours  = $this->businessHoursToArray($businessHours);
    }

    public function addHoursTo(CarbonInterface $date, string $duration): CarbonInterface
    {
        // split hours/minutes
        preg_match('/^(\d{2}):(\d{2})$/', $duration, $matches);
        $hours   = (int) $matches[1];
        $minutes = (int) $matches[2];

        $date->setTimezone($this->businessHours->getTimezone());
        $dayOfWeek = strtolower($date->isoFormat('ddd'));

        $remainingHours = $hours;
        while ($remainingHours > 0) {
            // get active status of current day
            $active = $this->workingHours[$dayOfWeek]['active'] ?? false;

            // is current day inactive?
            if ($active === false) {
                while ($active === false) {
                    // add day
                    $date->addDay();
                    // get day of week
                    $dayOfWeek = strtolower($date->isoFormat('ddd'));

                    // is active?
                    $active = $this->workingHours[$dayOfWeek]['active'] ?? false;
                }

                // set hour to beginning of day
                $date->setHour($this->workingHours[$dayOfWeek]['startHour']);
                $date->setMinute($this->workingHours[$dayOfWeek]['startMinute']);
                $date->setSecond(0);
            }

            // passed end of day working hours?
            if ($date->hour >= $this->workingHours[$dayOfWeek]['endHour']) {
                // set hour to beginning of day
                $date->setHour($this->workingHours[$dayOfWeek]['startHour']);
                $date->setMinute($this->workingHours[$dayOfWeek]['startMinute']);
                $date->addDay();
            }

            // add 1 hour
            $date->addHour();

            // remove 1 hour from remaining hours
            $remainingHours--;
        }

        $date = $this->addMinutesTo($date, $minutes);

        die($date);
        return $date;
    }

    public function addMinutesTo(CarbonInterface $date, int $minutes): CarbonInterface
    {
        // is current day inactive?
        $dayOfWeek = strtolower($date->isoFormat('ddd'));
        $active    = $this->workingHours[$dayOfWeek]['active'] ?? false;

        $remainingMinutes = $minutes;
        while ($remainingMinutes > 0) {
            if ($active === false) {
                while ($active === false) {
                    // add day
                    $date->addDay();
                    // get day of week
                    $dayOfWeek = strtolower($date->isoFormat('ddd'));

                    // is active?
                    $active = $this->workingHours[$dayOfWeek]['active'] ?? false;
                }

                // set hour to beginning of day
                $date->setHour($this->workingHours[$dayOfWeek]['startHour']);
                $date->setMinute($this->workingHours[$dayOfWeek]['startMinute']);
                $date->setSecond(0);
            }

            // passed end of day working hours?
            if ($date->hour >= $this->workingHours[$dayOfWeek]['endHour']) {
                // set hour to beginning of day
                $date->setHour($this->workingHours[$dayOfWeek]['startHour']);
                $date->setMinute($this->workingHours[$dayOfWeek]['startMinute']);
                $date->addDay();
            }

            $date->addMinute();
            $remainingMinutes--;
        }

        return $date;
    }

    public function getHoursBetweenDates(Carbon $from, Carbon $to): int
    {
        if ($to->lte($from)) {
            return 0;
        }

        $startHour = array_map('intval', explode(':', $this->options['start']))[0];
        $endHour   = array_map('intval', explode(':', $this->options['end']))[0];

        $period = new CarbonPeriod($from, '1 hour', $to);
        $period->excludeStartDate();

        $hours = 0;
        foreach ($period as $date) {
            if ($date->isWeekend() || $date->hour <= $startHour || $date->hour > $endHour) {
                continue;
            }

            $hours++;
        }
        return $hours;
    }

    public function getHoursFromDate(Carbon $start): int
    {
        return $this->getHoursBetweenDates($start, Carbon::now());
    }

    public static function getHoursFromFloat(float $float, string $delim = '.'): int
    {
        $float = number_format($float, 0, $delim, '');
        return (int) $float;
    }

    public static function getMinutesFromFloat(float $float, string $delim = '.'): int
    {
        $float = number_format($float, 2, $delim, '');
        $parts = explode($delim, $float);
        if (is_array($parts) && count($parts) === 2) {
            return (int) $parts[1];
        }

        return 0;
    }

    /**
     * Convert BusinessDays entity into usable array:
     *   result [mon => [start => hh:ii, end=> hh:ii, active => true]]
     *
     * @param BusinessHours $businessHours Business hours entity
     * @return array business hours as array
     */
    protected function businessHoursToArray(BusinessHours $businessHours): array
    {
        $days = $businessHours->getArrayCopy();

        $keys = array_keys($days);
        foreach ($keys as $key) {
            if (strpos($key, '_') !== false) {
                // split key value (ie mon_start becomes [mon,start])
                $parts = explode('_', $key);

                // if array key is "active", convert value to bool
                if (strcmp($parts[1], 'active') === 0) {
                    $days[$key] = (bool) $days[$key];
                } elseif (strcmp($parts[1], 'start') === 0) {
                    // if starting time, extract start hour/minute
                    if ($start = $this->getHoursAndMinutesFromString($days[$key])) {
                        $this->workingHours[$parts[0]]['startHour']   = (int) $start['hours'];
                        $this->workingHours[$parts[0]]['startMinute'] = (int) $start['minutes'];
                    }
                } elseif (strcmp($parts[1], 'end') === 0) {
                    // if ending time, extract end time/minute
                    if ($start = $this->getHoursAndMinutesFromString($days[$key])) {
                        $this->workingHours[$parts[0]]['endHour']   = (int) $start['hours'];
                        $this->workingHours[$parts[0]]['endMinute'] = (int) $start['minutes'];
                    }
                }

                // form array [mon => start => hh:ii]]
                $this->workingHours[$parts[0]][$parts[1]] = $days[$key];
            }
        }

        return $this->workingHours;
    }

    protected function getHoursAndMinutesFromString(?string $string): ?array
    {
        if ($string === null) {
            return null;
        }
        if (! preg_match('/^(\d{2}):(\d{2})$/', $string, $matches)) {
            return null;
        }

        return [
            'hours'   => $matches[1],
            'minutes' => $matches[2],
        ];
    }
}
