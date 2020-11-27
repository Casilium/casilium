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
        $element->setLabel('Applies to')
            ->setAttributes([
                'id'       => 'applies_to',
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

        $element = new Element\Number('p_low_response_time');
        $element->setLabel('Respond Time (low priority)')
            ->setAttributes([
                'class' => 'form_control',
            ]);
        $this->add($element);

        $element = new Element\Number('p_low_resolve_time');
        $element->setLabel('Resolve time (low priority)')
            ->setAttributes([
                'class' => 'form_control',
            ]);
        $this->add($element);

        $element = new Element\Number('p_medium_response_time');
        $element->setLabel('Respond Time (medium priority)')
            ->setAttributes([
                'class' => 'form_control',
            ]);
        $this->add($element);

        $element = new Element\Number('p_medium_resolve_time');
        $element->setLabel('Resolve time (medium priority)')
            ->setAttributes([
                'class' => 'form_control',
            ]);
        $this->add($element);

        $element = new Element\Number('p_high_response_time');
        $element->setLabel('Respond Time (high priority)')
            ->setAttributes([
                'class' => 'form_control',
            ]);
        $this->add($element);

        $element = new Element\Number('p_high_resolve_time');
        $element->setLabel('Resolve time (high priority)')
            ->setAttributes([
                'class' => 'form_control',
            ]);
        $this->add($element);

        $element = new Element\Number('p_urgent_response_time');
        $element->setLabel('Respond Time (urgent priority)')
            ->setAttributes([
                'class' => 'form_control',
            ]);
        $this->add($element);

        $element = new Element\Number('p_urgent_resolve_time');
        $element->setLabel('Resolve time (urgent priority)')
            ->setAttributes([
                'class' => 'form_control',
            ]);
        $this->add($element);

        $element = new Element\Number('p_critical_response_time');
        $element->setLabel('Respond Time (critical priority)')
            ->setAttributes([
                'class' => 'form_control',
            ]);
        $this->add($element);

        $element = new Element\Number('p_critical_resolve_time');
        $element->setLabel('Resolve time (critical priority)')
            ->setAttributes([
                'class' => 'form_control',
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
        ];
    }
}
