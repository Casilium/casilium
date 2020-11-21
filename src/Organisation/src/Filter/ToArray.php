<?php

declare(strict_types=1);

namespace Organisation\Filter;

use Laminas\Filter\AbstractFilter;

class ToArray extends AbstractFilter
{
    /**
     * @param mixed $value
     * @return mixed|void
     */
    public function filter($value): array
    {
        return array_map('trim', explode(';', $value));
    }
}