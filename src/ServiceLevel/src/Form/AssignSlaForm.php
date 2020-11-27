<?php

declare(strict_types=1);

namespace ServiceLevel\Form;

use Laminas\Filter;
use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator;

class AssignSlaForm extends Form implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('assign-sla');

        $this->addElements();
    }

    public function addElements(): void
    {
        $element = new Element\Select('sla_id');
        $element->setLabel('SLA')
            ->setDisableInArrayValidator(true)
            ->setEmptyOption('Please Select')
            ->setAttributes([
                'id'    => 'sla_id',
                'class' => 'form-control',
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
                'name'       => 'sla_id',
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