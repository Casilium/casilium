<?php

declare(strict_types=1);

namespace Ticket\InputFilter;

use Laminas\Filter;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator;

class SearchTicketInputFilter extends InputFilter
{
    public function init(): void
    {
        $this->add([
            'name'       => 'search_text',
            'required'   => false,
            'filters'    => [
                ['name' => Filter\StringTrim::class],
                ['name' => Filter\StripTags::class],
            ],
            'validators' => [
                [
                    'name'    => Validator\StringLength::class,
                    'options' => [
                        'max' => 255,
                    ],
                ],
            ],
        ]);

        $this->add([
            'name'       => 'organisation_uuid',
            'required'   => false,
            'filters'    => [
                ['name' => Filter\StringTrim::class],
                ['name' => Filter\StripTags::class],
            ],
            'validators' => [
                [
                    'name'    => Validator\Regex::class,
                    'options' => [
                        'pattern' => '/^[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}$/i',
                    ],
                ],
            ],
        ]);

        $this->add([
            'name'       => 'contact_id',
            'required'   => false,
            'filters'    => [
                ['name' => Filter\ToInt::class],
            ],
            'validators' => [
                ['name' => Validator\Digits::class],
            ],
        ]);

        $this->add([
            'name'       => 'queue_id',
            'required'   => false,
            'filters'    => [
                ['name' => Filter\ToInt::class],
            ],
            'validators' => [
                ['name' => Validator\Digits::class],
            ],
        ]);

        $this->add([
            'name'       => 'date_from',
            'required'   => false,
            'filters'    => [
                ['name' => Filter\StringTrim::class],
                ['name' => Filter\StripTags::class],
            ],
            'validators' => [
                [
                    'name'    => Validator\Date::class,
                    'options' => [
                        'format' => 'Y-m-d',
                    ],
                ],
            ],
        ]);

        $this->add([
            'name'       => 'date_to',
            'required'   => false,
            'filters'    => [
                ['name' => Filter\StringTrim::class],
                ['name' => Filter\StripTags::class],
            ],
            'validators' => [
                [
                    'name'    => Validator\Date::class,
                    'options' => [
                        'format' => 'Y-m-d',
                    ],
                ],
            ],
        ]);
    }
}
