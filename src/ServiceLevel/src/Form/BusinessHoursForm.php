<?php

declare(strict_types=1);

namespace ServiceLevel\Form;

use DateTimeZone;
use Laminas\Filter;
use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator;
use ServiceLevel\Form\Element\Time;

class BusinessHoursForm extends Form implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('business-hours');

        $this->setAttributes([
            'role' => 'form',
        ]);
        $this->addFormElements();
    }

    public function addFormElements(): void
    {
        $element = new Element\Text('id');
        $element->setAttributes([
            'id'    => 'id',
            'class' => 'form-control',
        ]);
        $this->add($element);

        $element = new Element\Text('name');
        $element->setAttributes([
            'id'          => 'name',
            'class'       => 'form-control',
            'placeholder' => 'Standard business hours',
        ]);
        $element->setLabel('Name');
        $this->add($element);

        $element = new Element\Select('timezone');
        $element->setLabel('Timezone')
            ->setAttributes([
                'id'    => 'timezone',
                'class' => 'custom-select',
            ])
            ->setValueOptions(DateTimeZone::listIdentifiers(DateTimeZone::ALL))
            ->setValue(341);
        $this->add($element);

        $element = new Time('mon_start');
        $element->setLabel('Monday');
        $this->add($element);

        $element = new Time('mon_end');
        $element->setLabel('To');
        $this->add($element);

        $element = new Time('tue_start');
        $element->setLabel('Tuesday');
        $this->add($element);

        $element = new Time('tue_end');
        $element->setLabel('To');
        $this->add($element);

        $element = new Time('wed_start');
        $element->setLabel('Wednesday');
        $this->add($element);

        $element = new Time('wed_end');
        $element->setLabel('To');
        $this->add($element);

        $element = new Time('thu_start');
        $element->setLabel('Thursday');
        $this->add($element);

        $element = new Time('thu_end');
        $element->setLabel('To');
        $this->add($element);

        $element = new Time('fri_start');
        $element->setLabel('Friday');
        $this->add($element);

        $element = new Time('fri_end');
        $element->setLabel('To');
        $this->add($element);

        $element = new Time('sat_start');
        $element->setLabel('Saturday');
        $this->add($element);

        $element = new Time('sat_end');
        $element->setLabel('To');
        $this->add($element);

        $element = new Time('sun_start');
        $element->setLabel('Sunday');
        $this->add($element);

        $element = new Time('sun_end');
        $element->setLabel('To');
        $this->add($element);

        $element = new Element\Checkbox('mon_active');
        $element->setCheckedValue(1)
            ->setUncheckedValue(0)
            ->setUseHiddenElement(true)
            ->setAttributes([
                'class' => 'form-check-input',
                'value' => 1,
            ]);
        $this->add($element);

        $element = new Element\Checkbox('tue_active');
        $element->setCheckedValue(1)
            ->setUncheckedValue(0)
            ->setUseHiddenElement(true)
            ->setAttributes([
                'class' => 'form-check-input',
                'value' => 1,
            ]);
        $this->add($element);

        $element = new Element\Checkbox('wed_active');
        $element->setCheckedValue(1)
            ->setUncheckedValue(0)
            ->setUseHiddenElement(true)
            ->setAttributes([
                'class' => 'form-check-input',
                'value' => 1,
            ]);
        $this->add($element);

        $element = new Element\Checkbox('thu_active');
        $element->setCheckedValue(1)
            ->setUncheckedValue(0)
            ->setUseHiddenElement(true)
            ->setAttributes([
                'class' => 'form-check-input',
                'value' => 1,
            ]);
        $this->add($element);

        $element = new Element\Checkbox('fri_active');
        $element->setCheckedValue(1)
            ->setUncheckedValue(0)
            ->setUseHiddenElement(true)
            ->setAttributes([
                'class' => 'form-check-input',
                'value' => 1,
            ]);
        $this->add($element);

        $element = new Element\Checkbox('sat_active');
        $element->setCheckedValue(1)
            ->setUncheckedValue(0)
            ->setUseHiddenElement(true)
            ->setAttributes([
                'class' => 'form-check-input',
                'value' => 0,
            ]);
        $this->add($element);

        $element = new Element\Checkbox('sun_active');
        $element->setCheckedValue(1)
            ->setUncheckedValue(0)
            ->setUseHiddenElement(true)
            ->setAttributes([
                'class' => 'form-check-input',
                'value' => 0,
            ]);
        $this->add($element);

        $element = new Element\Submit('submit');
        $element->setValue('Submit')
            ->setAttributes([
                'class' => 'btn btn-primary',
            ]);
        $this->add($element);
    }

    public function getInputFilterSpecification(): array
    {
        return [
            [
                'name'       => 'id',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\ToInt::class],
                ],
                'validators' => [
                    ['name' => Validator\Digits::class],
                ],
            ],
            [
                'name'     => 'name',
                'required' => true,
                'filters'  => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
            ],
            [
                'name'       => 'timezone',
                'required'   => true,
                'filters'    => [
                    ['name' => Filter\ToInt::class],
                ],
                'validators' => [
                    ['name' => Validator\Digits::class],
                ],
            ],
            [
                'name'       => 'mon_start',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern' => '/([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?/',
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'mon_end',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern' => '/([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?/',
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'tue_start',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern' => '/([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?/',
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'tue_end',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern' => '/([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?/',
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'wed_start',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern' => '/([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?/',
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'wed_end',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern' => '/([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?/',
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'thu_start',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern' => '/([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?/',
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'thu_end',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern' => '/([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?/',
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'fri_start',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern' => '/([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?/',
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'fri_end',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern' => '/([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?/',
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'sat_start',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern' => '/([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?/',
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'sat_end',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern' => '/([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?/',
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'sun_start',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern' => '/([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?/',
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'sun_end',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern' => '/([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?/',
                        ],
                    ],
                ],
            ],
        ];
    }
}
