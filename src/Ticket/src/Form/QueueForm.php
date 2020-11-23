<?php

declare(strict_types=1);

namespace Ticket\Form;

use Laminas\Filter;
use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator;

class QueueForm extends Form implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('queue-form');
        $this->addFormElements();
    }

    public function addFormElements(): void
    {
        $element = new Element\Hidden('id');
        $this->add($element);

        $element = new Element\Text('name');
        $element->setLabel('Name')
            ->setAttributes([
                'class'       => 'form-control',
                'id'          => 'name',
                'placeholder' => 'Queue Name',
            ]);
        $this->add($element);

        $element = new Element\Email('email');
        $element->setLabel('Email')
            ->setAttributes([
                'class'       => 'form-control',
                'id'          => 'email',
                'placeholder' => 'Enter email address',
            ]);
        $this->add($element);

        $element = new Element\Text('host');
        $element->setLabel('Server address')
            ->setAttributes([
                'class'       => 'form-control',
                'id'          => 'host',
                'placeholder' => 'imap.example.com',
            ]);
        $this->add($element);

        $element = new Element\Text('user');
        $element->setLabel('Username')
            ->setAttributes([
                'class'       => 'form-control',
                'id'          => 'user',
                'placeholder' => 'name@example.com',
            ]);
        $this->add($element);

        $element = new Element\Password('password');
        $element->setLabel('Password')
            ->setAttributes([
                'class'       => 'form-control',
                'id'          => 'password',
                'placeholder' => 'password',
            ]);
        $this->add($element);

        $element = new Element\Password('confirm_password');
        $element->setLabel('Confirm password')
            ->setAttributes([
                'class'       => 'form-control',
                'id'          => 'confirm_password',
                'placeholder' => 'Confirm password',
            ]);
        $this->add($element);

        $element = new Element\Checkbox('use_ssl');
        $element->setLabel('Use SSL?')
            ->setAttributes([
                'class' => 'form-check-input',
                'id'    => 'ssl',
            ])->setLabelAttributes([
                'class' => 'form-check-label',
            ])->setLabelAttributes([
                'class' => 'form-check-label',
            ])->setOptions([
                'checked_value'   => 1,
                'unchecked_value' => 0,
            ]);
        $this->add($element);

        $element = new Element\Checkbox('fetch_from_mail');
        $element->setLabel('Create tickets from mail?')
            ->setAttributes([
                'class' => 'form-check-input',
                'id'    => 'fetch',
            ])->setLabelAttributes([
                'class' => 'form-check-label',
            ])->setOptions([
                'checked_value'   => 1,
                'unchecked_value' => 0,
            ]);
        $this->add($element);

        $element = new Element\Submit('submit');
        $element->setAttributes([
            'class' => 'btn btn-primary',
            'id'    => 'submit',
            'value' => 'Submit',
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
                'name'       => 'name',
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
                        'name'                   => Validator\StringLength::class,
                        'options'                => [
                            'min' => 3,
                            'max' => 255,
                        ],
                        'break_chain_on_failure' => true,
                    ],
                ],
            ],
            [
                'name'       => 'email',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StringTrim::class],
                    ['name' => Filter\StripTags::class],
                ],
                'validators' => [
                    [
                        'name'                   => Validator\EmailAddress::class,
                        'break_chain_on_failure' => true,
                    ],
                ],
            ],
            [
                'name'       => 'host',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    ['name' => Validator\Hostname::class],
                ],
            ],
            [
                'name'       => 'user',
                'required'   => false,
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
                        'name'                   => Validator\StringLength::class,
                        'options'                => [
                            'min' => 3,
                            'max' => 255,
                        ],
                        'break_chain_on_failure' => true,
                    ],
                ],
            ],
            [
                'name'       => 'password',
                'required'   => false,
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
                        'name'                   => Validator\StringLength::class,
                        'options'                => [
                            'min' => 3,
                            'max' => 255,
                        ],
                        'break_chain_on_failure' => true,
                    ],
                ],
            ],
            [
                'name'       => 'confirm_password',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StringTrim::class],
                    ['name' => Filter\StripTags::class],
                ],
                'validators' => [
                    [
                        'name'                   => Validator\Identical::class,
                        'options'                => [
                            'token'    => 'password',
                            'messages' => [
                                Validator\Identical::NOT_SAME      => 'Passwords do not match',
                                Validator\Identical::MISSING_TOKEN => 'Enter password in both fields',
                            ],
                        ],
                        'break_chain_on_failure' => true,
                    ],
                ],
            ],
            [
                'name'       => 'ssl',
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
                'name'       => 'fetch',
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
        ];
    }
}
