<?php

declare(strict_types=1);

namespace Organisation\Form;

use Laminas\Filter;
use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator;

class SelectForm extends Form implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('organisation_select');

        $this->addFormElements();
    }

    public function addFormElements(): void
    {
        $element = new Element\Select('organisation');
        $element->setLabel('Organisation');
        $element->setAttributes([
            'id'          => 'organisation',
            'class'       => 'form-control basicAutoSelect',
            'placeholder' => 'type to search... ',
            'data-url'    => '/organisation/select',
        ]);
        $element->setDisableInArrayValidator(true);
        $this->add($element);

        $element = new Element\Submit();
        $element->setName('submit')
            ->setValue('Submit')
            ->setAttribute('class', 'btn btn-primary');
        $this->add($element);
    }

    public function getInputFilterSpecification(): array
    {
        return [
            [
                'name'       => 'organisation',
                'required'   => true,
                'filters'    => [
                    ['name' => Filter\ToInt::class],
                ],
                'validators' => [
                    ['name' => Validator\Digits::class],
                ],
            ],
        ];
    }
}
