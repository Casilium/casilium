<?php

declare(strict_types=1);

namespace ServiceLevel\Form;

use Laminas\Filter;
use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator;

class SlaForm extends Form implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('sla-form');

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
            'placeholder' => 'Enter SLA policy name',
        ]);
        $element->setLabel('Name');
        $this->add($element);

        $element = new Element\Select('business_hours');
        $element->setLabel('Business Hours')
            ->setAttributes([
                'id'       => 'business_hours',
                'class'    => 'form-control',
                'required' => true,
            ])
            ->setDisableInArrayValidator(true)
            ->setEmptyOption('Please Select');
        $this->add($element);

        $element = new Element\Submit('submit');
        $element->setValue('Submit')
            ->setAttributes([
                'class' => 'btn btn-primary',
            ]);
        $this->add($element);

        $element = new Element\Text('p_low_response_time');
        $element->setLabel('Respond Time (low priority)')
            ->setAttributes([
                'class'       => 'form_control',
                'placeholder' => 'hh:mm',
            ]);
        $this->add($element);

        $element = new Element\Text('p_low_resolve_time');
        $element->setLabel('Resolve time (low priority)')
            ->setAttributes([
                'class'       => 'form_control',
                'placeholder' => 'hh:mm',
            ]);
        $this->add($element);

        $element = new Element\Text('p_medium_response_time');
        $element->setLabel('Respond Time (medium priority)')
            ->setAttributes([
                'class'       => 'form_control',
                'placeholder' => 'hh:mm',
            ]);
        $this->add($element);

        $element = new Element\Text('p_medium_resolve_time');
        $element->setLabel('Resolve time (medium priority)')
            ->setAttributes([
                'class'       => 'form_control',
                'placeholder' => 'hh:mm',
            ]);
        $this->add($element);

        $element = new Element\Text('p_high_response_time');
        $element->setLabel('Respond Time (high priority)')
            ->setAttributes([
                'class'       => 'form_control',
                'placeholder' => 'hh:mm',
            ]);
        $this->add($element);

        $element = new Element\Text('p_high_resolve_time');
        $element->setLabel('Resolve time (high priority)')
            ->setAttributes([
                'class'       => 'form_control',
                'placeholder' => 'hh:mm',
            ]);
        $this->add($element);

        $element = new Element\Text('p_urgent_response_time');
        $element->setLabel('Respond Time (urgent priority)')
            ->setAttributes([
                'class'       => 'form_control',
                'placeholder' => 'hh:mm',
            ]);
        $this->add($element);

        $element = new Element\Text('p_urgent_resolve_time');
        $element->setLabel('Resolve time (urgent priority)')
            ->setAttributes([
                'class'       => 'form_control',
                'placeholder' => 'hh:mm',
            ]);
        $this->add($element);

        $element = new Element\Text('p_critical_response_time');
        $element->setLabel('Respond Time (critical priority)')
            ->setAttributes([
                'class'       => 'form_control',
                'placeholder' => 'hh:mm',
            ]);
        $this->add($element);

        $element = new Element\Text('p_critical_resolve_time');
        $element->setLabel('Resolve time (critical priority)')
            ->setAttributes([
                'class'       => 'form_control',
                'placeholder' => 'hh:mm',
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
                'name'       => 'p_low_response_time',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern' => '/^\d{2}:\d{2}$/',
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'p_low_resolve_time',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern' => '/^\d{2}:\d{2}$/',
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'p_medium_response_time',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern' => '/^\d{2}:\d{2}$/',
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'p_medium_resolve_time',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern' => '/^\d{2}:\d{2}$/',
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'p_high_response_time',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern' => '/^\d{2}:\d{2}$/',
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'p_high_resolve_time',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern' => '/^\d{2}:\d{2}$/',
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'p_urgent_response_time',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern' => '/^\d{2}:\d{2}$/',
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'p_urgent_resolve_time',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern' => '/^\d{2}:\d{2}$/',
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'p_critical_response_time',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern' => '/^\d{2}:\d{2}$/',
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'p_critical_resolve_time',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern' => '/^\d{2}:\d{2}$/',
                        ],
                    ],
                ],
            ],
        ];
    }
}
