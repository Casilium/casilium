<?php
declare(strict_types=1);

namespace OrganisationContact\Form;

use Laminas\Filter;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator;
use OrganisationContact\Validator\PhoneNumberValidator;

class ContactForm extends Form implements InputFilterProviderInterface
{
    public function __construct()
    {
        // specify our own form name
        parent::__construct('contact-form');

        // form attributes
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal');

        $this->addElements();
    }

    public function addElements(): void
    {
        // id
        $this->add([
            'name'      => 'id',
            'attribute' => [
                'type' => 'hidden',
                'id'   => 'id',
            ],
        ]);

        // first name
        $this->add([
            'name'       => 'first_name',
            'attributes' => [
                'type'         => 'text',
                'id'           => 'firstName',
                'class'        => 'form-control',
                'required'     => true,
                'autocomplete' => 'off',
            ],
            'options'    => [
                'label' => 'First name',
            ],
        ]);

        // middle name
        $this->add([
            'name'       => 'middle_name',
            'attributes' => [
                'type'         => 'text',
                'id'           => 'middleName',
                'class'        => 'form-control',
                'autocomplete' => 'off',
            ],
            'options'    => [
                'label' => 'Middle Name',
            ],
        ]);

        // last name
        $this->add([
            'name'       => 'last_name',
            'attributes' => [
                'type'         => 'text',
                'id'           => 'lastName',
                'class'        => 'form-control',
                'required'     => true,
                'autocomplete' => 'off',
            ],
            'options'    => [
                'label' => 'Surname',
            ],
        ]);

        // work telephone
        $this->add([
            'name'       => 'work_telephone',
            'attributes' => [
                'type'         => 'text',
                'id'           => 'workTelephone',
                'class'        => 'form-control',
                'autocomplete' => 'off',
            ],
            'options'    => [
                'label' => 'Work telephone',
            ],
        ]);

        // work extension
        $this->add([
            'name'       => 'work_extension',
            'attributes' => [
                'type'         => 'text',
                'id'           => 'workExtension',
                'class'        => 'form-control',
                'autocomplete' => 'off',
            ],
            'options'    => [
                'label' => 'Extension',
            ],
        ]);

        // home telephone
        $this->add([
            'name'       => 'home_telephone',
            'attributes' => [
                'type'         => 'text',
                'id'           => 'homeTelephone',
                'class'        => 'form-control',
                'autocomplete' => 'off',
            ],
            'options'    => [
                'label' => 'Home telephone',
            ],
        ]);

        // mobile telephone
        $this->add([
            'name'       => 'mobile_telephone',
            'attributes' => [
                'type'         => 'text',
                'id'           => 'mobileTelephone',
                'class'        => 'form-control',
                'autocomplete' => 'off',
            ],
            'options'    => [
                'label' => 'Mobile telephone',
            ],
        ]);

        // work email
        $this->add([
            'name'       => 'work_email',
            'attributes' => [
                'type'         => 'text',
                'id'           => 'workEmail',
                'class'        => 'form-control',
                'autocomplete' => 'off',
                'required'     => true,
            ],
            'options'    => [
                'label' => 'Work email',
            ],
        ]);

        // work email
        $this->add([
            'name'       => 'other_email',
            'attributes' => [
                'type'         => 'text',
                'id'           => 'otherEmail',
                'class'        => 'form-control',
                'autocomplete' => 'off',
            ],
            'options'    => [
                'label' => 'Other email',
            ],
        ]);

        // gender
        $this->add([
            'name'       => 'gender',
            'attributes' => [
                'type'         => 'text',
                'id'           => 'gender',
                'class'        => 'form-control',
                'autocomplete' => 'off',
            ],
            'options'    => [
                'label' => 'Gender',
            ],
        ]);

        // submit
        $this->add([
            'name'       => 'submit',
            'attributes' => [
                'type'  => 'submit',
                'value' => 'Save',
                'id'    => 'submitButton',
                'class' => 'btn btn-primary',
            ],
        ]);
    }

    public function getInputFilterSpecification(): array
    {
        return [
            [
                'name'       => 'id',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Digits::class,
                        'options' => [
                            'messages' => [
                                Validator\Digits::NOT_DIGITS => 'ID field may only contain digits',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'first_name',
                'required'   => true,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern'  => '/^[a-z0-9_ \'\-,]{3,32}$/i',
                            'messages' => [
                                Validator\Regex::NOT_MATCH
                                    => 'Name can only container letters, numbers, and hyphens; '
                                    . 'must be between 3 and 64 characters',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'middle_name',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern'  => '/^[a-z0-9_ \'\-,]{3,32}$/i',
                            'messages' => [
                                Validator\Regex::NOT_MATCH
                                    => 'Middle Name can only container letters, numbers, and hyphens; '
                                    . 'must be between 3 and 64 characters',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'last_name',
                'required'   => true,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern'  => '/^[a-z0-9_ \'\-,]{3,32}$/i',
                            'messages' => [
                                Validator\Regex::NOT_MATCH
                                    => 'Name can only container letters, numbers, and hyphens; '
                                    . 'must be between 3 and 64 characters',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'work_telephone',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    ['name' => PhoneNumberValidator::class],
                ],
            ],
            [
                'name'       => 'work_extension',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern'  => '/^[0-9]{2,8}$/i',
                            'messages' => [
                                Validator\Regex::NOT_MATCH
                                    => 'Telephone can only container numbers and '
                                    . 'must be between 3 and 10 characters',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'mobile_telephone',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    ['name' => PhoneNumberValidator::class],
                ],
            ],
            [
                'name'       => 'home_telephone',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    ['name' => PhoneNumberValidator::class],
                ],
            ],
            [
                'name'       => 'work_email',
                'required'   => true,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name' => Validator\EmailAddress::class,
                    ],
                ],
            ],
            [
                'name'       => 'other_email',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name' => Validator\EmailAddress::class,
                    ],
                ],
            ],
        ];
    }
}
