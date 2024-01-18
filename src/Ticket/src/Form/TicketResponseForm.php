<?php

declare(strict_types=1);

namespace Ticket\Form;

use Laminas\Filter;
use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator;

class TicketResponseForm extends Form implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('ticket-response');

        $this->addElements();
    }

    public function addElements(): void
    {
        $element = new Element\Text('id');
        $this->add($element);

        $element = new Element\Textarea('response');
        $element->setAttributes(['class' => 'form-control'])
            ->setLabel('Response');
        $this->add($element);

        $element = new Element\Checkbox('is_public');
        $element
            ->setLabel('Public')
            ->setAttributes([
                'id'    => 'is_public',
                'class' => 'custom-control-input',
            ])
            ->setLabelAttributes([
                'class' => 'custom-control-label',
            ])
            ->setOptions([
                'check_value'     => '1',
                'unchecked_value' => '0',
            ])
            ->setValue(1);
        $this->add($element);

        $element = new Element\Submit('submit');
        $element->setLabel('Response')
            ->setAttributes(['class' => 'btn btn-primary'])
            ->setValue('Save');

        $this->add($element);
    }

    public function getInputFilterSpecification(): array
    {
        return [
            [
                'name'       => 'id',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StringTrim::class],
                    ['name' => Filter\StripTags::class],
                ],
                'validators' => [
                    [
                        'name' => Validator\Digits::class,
                    ],
                ],
            ],
            [
                'name'       => 'response',
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
                            'min' => 8,
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'is_public',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\ToInt::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Between::class,
                        'options' => [
                            'min'       => 0,
                            'max'       => 1,
                            'inclusive' => true,
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'submit',
                'required'   => true,
                'filters'    => [
                    ['name' => Filter\StringTrim::class],
                    ['name' => Filter\StripTags::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern'  => '/^(save|save_hold|save_resolve)$/',
                            'messages' => [
                                Validator\Regex::NOT_MATCH => 'Invalid submit value',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
