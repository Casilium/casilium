<?php

declare(strict_types=1);

namespace Ticket\Form;

use Laminas\Filter;
use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator;
use Override;

/**
 * @extends Form<array{
 *     id?: string,
 *     response: string,
 *     is_public?: int,
 *     submit: string
 * }>
 */
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
        $element->setAttributes([
            'class'              => 'form-control',
            'data-paste-cleanup' => 'true',
            'id'                 => 'response',
        ])
            ->setLabel('Response');
        $this->add($element);

        $element = new Element\Checkbox('is_public');
        $element->setLabel('Public');
        $element->setAttributes([
            'id'    => 'is_public',
            'class' => 'custom-control-input',
        ]);
        $element->setLabelAttributes([
            'class' => 'custom-control-label',
        ]);
        $element->setOptions([
            'check_value'     => '1',
            'unchecked_value' => '0',
        ]);
        $element->setValue(1);
        $this->add($element);

        $element = new Element\Submit('submit');
        $element->setLabel('Response')
            ->setAttributes(['class' => 'btn btn-primary'])
            ->setValue('Save');

        $this->add($element);
    }

    #[Override]
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
                    [
                        'name'    => Filter\PregReplace::class,
                        'options' => [
                            'pattern'     => '/(\r?\n\s*){3,}/',
                            'replacement' => "\n\n",
                        ],
                    ],
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
                        'name'    => Validator\InArray::class,
                        'options' => [
                            'haystack' => [0, 1],
                            'strict'   => Validator\InArray::COMPARE_STRICT,
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
