<?php
declare(strict_types=1);

namespace Ticket\Form;

use Laminas\Filter;
use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator;
use Ticket\Validator\DateTimeValidator;

class TicketForm extends Form implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('create-ticket-form');

        $this->addElements();
    }

    public function addElements(): void
    {
        $element = new Element\Select('contact_id');
        $element->setLabel('Request User')
            ->setAttributes(['id' => 'contact_id', 'class' => 'form-control', 'required' => true])
            ->setDisableInArrayValidator(true)
            ->setEmptyOption('Please Select');
        $this->add($element);

        $element = new Element\Text('id');
        $element->setAttributes(['class' => 'form-control', 'required' => false]);
        $this->add($element);

        $element = new Element\Textarea('long_description');
        $element->setLabel('Long Description')
            ->setAttributes([
                'id'          => 'long_description',
                'rows'        => 5,
                'class'       => 'form-control',
                'placeholder' => 'Enter a more comprehensive description of the problem',
            ]);
        $this->add($element);

        $element = new Element\Select('impact');
        $element->setLabel('Impact')
            ->setAttributes(['id' => 'impact', 'class' => 'form-control', 'required' => true])
            //->setEmptyOption('Please Select Impact')
            ->setValueOptions([
                '3' => 'Low (single user)',
                '2' => 'Medium (multiple users)',
                '1' => 'High (everyone)',
            ]);
        $this->add($element);

        $element = new Element\Select('queue_id');
        $element->setLabel('Queue')
            ->setEmptyOption('Please Select')
            ->setAttributes(['id' => 'queue_id', 'class' => 'form-control', 'required' => true])
            ->setDisableInArrayValidator(true);
        $this->add($element);

        $element = new Element\Text('short_description');
        $element->setLabel('Summary')
            ->setAttributes([
                'id'          => 'short_description',
                'class'       => 'form-control',
                'placeholder' => 'Ticket summary',
                'required'    => true,
            ]);
        $this->add($element);

        $element = new Element\Hidden('agent_id');
        $this->add($element);

        $element = new Element\Select('site_id');
        $element->setLabel('Site')
            ->setEmptyOption('Please Select')
            ->setAttributes(['id' => 'site_id', 'class' => 'form-control', 'required' => true])
            ->setDisableInArrayValidator(true);
        $this->add($element);

        $element = new Element\Select('source');
        $element->setLabel('Ticket Source')
            ->setAttributes(['id' => 'source', 'class' => 'form-control', 'required' => true])
            ->setEmptyOption('Please Select Source')
            ->setValueOptions([
                '1' => 'E-Mail',
                '2' => 'Phone',
                '3' => 'Web',
            ]);
        $this->add($element);

        $element = new Element\Text('start_date');
        $element->setLabel('Due By')
            ->setAttributes([
                'class'       => 'form-control datetimepicker-input',
                'data-target' => '#start_date_picker',
                'id'          => 'start_date',
            ]);
        $this->add($element);

        $element = new Element\Select('type_id');
        $element->setLabel('Type')
            ->setEmptyOption('Please Select')
            ->setAttributes(['id' => 'type_id', 'class' => 'form-control', 'required' => true])
            ->setValueOptions([
                '1' => 'Service Request',
                '2' => 'Incident',
            ])
            ->setValue(2)
            ->setDisableInArrayValidator(true);

        $this->add($element);

        $element = new Element\Select('urgency');
        $element->setLabel('Urgency')
            ->setAttributes(['id' => 'urgency', 'class' => 'form-control', 'required' => true])
            // ->setEmptyOption('Please Select Urgency')
            ->setValueOptions([
                '3' => 'Low (no significant impact)',
                '2' => 'Medium (work around available)',
                '1' => 'High (no workaround)',
            ]);
        $this->add($element);

        $element = new Element\Submit('submit');
        $element->setValue('Save')
            ->setAttributes(['class' => 'btn btn-primary']);
        $this->add($element);
    }

    public function getInputFilterSpecification(): array
    {
        return [
            [
                'name'       => 'contact_id',
                'required'   => true,
                'filters'    => [
                    ['name' => Filter\ToInt::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\GreaterThan::class,
                        'options' => [
                            'min' => 0,
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'id',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StringTrim::class],
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\ToInt::class],
                ],
                'validators' => [
                    [
                        'name' => Validator\Digits::class,
                    ],
                ],
            ],
            [
                'name'       => 'agent_id',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StringTrim::class],
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\ToInt::class],
                ],
                'validators' => [
                    [
                        'name' => Validator\Digits::class,
                    ],
                ],
            ],
            [
                'name'       => 'impact',
                'required'   => true,
                'filters'    => [
                    ['name' => Filter\ToInt::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\GreaterThan::class,
                        'options' => [
                            'min' => 0,
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'long_description',
                'required'   => true,
                'filters'    => [
                    ['name' => Filter\StringTrim::class],
                    ['name' => Filter\StripTags::class],
                ],
                'validators' => [
                    [
                        'name'                   => Validator\NotEmpty::class,
                        'break_chain_on_failure' => true,
                    ],
                ],
            ],
            [
                'name'       => 'queue_id',
                'required'   => true,
                'filters'    => [
                    ['name' => Filter\ToInt::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\GreaterThan::class,
                        'options' => [
                            'min' => 0,
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'short_description',
                'required'   => true,
                'filters'    => [
                    ['name' => Filter\StringTrim::class],
                    ['name' => Filter\StripTags::class],
                ],
                'validators' => [
                    [
                        'name'                   => Validator\NotEmpty::class,
                        'break_chain_on_failure' => true,
                    ],
                    [
                        'name'    => Validator\StringLength::class,
                        'options' => [
                            'min' => 3,
                            'max' => 255,
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'site_id',
                'required'   => true,
                'filters'    => [
                    ['name' => Filter\ToInt::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\GreaterThan::class,
                        'options' => [
                            'min' => 0,
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'source',
                'required'   => true,
                'filters'    => [
                    ['name' => Filter\ToInt::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\GreaterThan::class,
                        'options' => [
                            'min' => 0,
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'start_date',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StringTrim::class],
                    ['name' => Filter\StripTags::class],
                ],
                'validators' => [
                    ['name' => DateTimeValidator::class],
                ],
            ],
            [
                'name'       => 'type_id',
                'required'   => true,
                'filters'    => [
                    ['name' => Filter\ToInt::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\GreaterThan::class,
                        'options' => [
                            'min' => 0,
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'urgency',
                'required'   => true,
                'filters'    => [
                    ['name' => Filter\ToInt::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\GreaterThan::class,
                        'options' => [
                            'min' => 0,
                        ],
                    ],
                ],
            ],
        ];
    }
}
