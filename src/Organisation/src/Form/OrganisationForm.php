<?php

declare(strict_types=1);

namespace Organisation\Form;

use Laminas\Filter;
use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator;
use Organisation\Filter\ToArray;
use Organisation\Validator\DomainValidator;

/**
 * Organisation form for modifying organisation details
 */
class OrganisationForm extends Form implements InputFilterProviderInterface
{
    public function __construct(string $action = 'create')
    {
        parent::__construct('organisation-form');

        $this->setAttribute('method', 'post');
        $this->addElements($action);
    }

    /**
     * Add elements to form
     *
     * @param string $action
     */
    public function addElements($action = 'create'): void
    {
        $element = new Element\Text('domain');
        $element->setLabel('Domain(s)')->setAttributes([
            'id'          => 'domain',
            'class'       => 'form-control',
            'placeholder' => 'example.com',
        ]);
        $this->add($element);

        $element = new Element\Text();
        $element->setName('name')
            ->setLabel('Name')
            ->setAttributes([
                'autocomplete' => 'off',
                'class'        => 'form-control',
                'placeholder'  => 'Name of Organisation',
            ]);
        $this->add($element);

        if ($action === 'edit') {
            $element = new Element\Select('is_active');
            $element->setLabel('Status')
                ->setAttributes([
                    'class' => 'form-control',
                ])
                ->setValueOptions([
                    '1' => 'Active',
                    '0' => 'Inactive',
                ]);
            $this->add($element);
        }

        $element = new Element\Submit();
        $element->setName('submit')
            ->setValue('Create Organisation')
            ->setAttribute('class', 'btn btn-primary');
        $this->add($element);
    }

    /**
     * Input filter specification for validation and filtering
     *
     * @return array
     */
    public function getInputFilterSpecification(): array
    {
        return [
            [
                'name'       => 'name',
                'required'   => true,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern'  => '/^[a-zA-Z0-9_ -]{3,64}$/',
                            'messages' => [
                                Validator\Regex::NOT_MATCH
                                    => 'Name can only container letters, numbers, and hyphens; must be 3-64 characters',
                            ],
                        ],
                    ],
                ],
            ], // name
            [
                'name'       => 'domain',
                'required'   => true,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                    ['name' => ToArray::class],
                ],
                'validators' => [
                    ['name' => DomainValidator::class],
                ],
            ],
            [
                'name'     => 'is_active',
                'required' => false,
                'filters'  => [
                    ['name' => Filter\ToInt::class],
                ],
            ],
        ];
    }
}
