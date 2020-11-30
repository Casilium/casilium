<?php
declare(strict_types=1);

namespace OrganisationSite\Form;

use Laminas\Filter;
use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator;

class SiteForm extends Form implements InputFilterProviderInterface
{
    /** @var array */
    private $countries;

    /**
     * Passes in a list of countries to populate the country select element
     *
     * @param array $countries
     */
    public function __construct(array $countries)
    {
        parent::__construct('site-form');

        // save countries for later
        $this->countries = $countries;

        // add form elements
        $this->addElements();
    }

    public function addElements(): void
    {
        $element = new Element\Text('id');
        $this->add($element);

        $element = new Element\Text('name');
        $element->setLabel('Site name/identifier')
            ->setAttributes(['class' => 'form-control']);
        $this->add($element);

        $element = new Element\Text('street_address');
        $element->setLabel('Street address')
            ->setAttributes([
                'class'    => 'form-control',
                'required' => true,
            ]);
        $this->add($element);

        $element = new Element\Text('street_address2');
        $element->setLabel('Street address2')
            ->setAttributes(['class' => 'form-control']);
        $this->add($element);

        $element = new Element\Text('city');
        $element->setLabel('City')
            ->setAttributes([
                'class'    => 'form-control',
                'required' => true,
            ]);
        $this->add($element);

        $element = new Element\Text('town');
        $element->setLabel('Town')
            ->setAttributes(['class' => 'form-control']);
        $this->add($element);

        $element = new Element\Text('county');
        $element->setLabel('County/Province/State')
            ->setAttributes(['class' => 'form-control']);
        $this->add($element);

        $element = new Element\Text('postal_code');
        $element->setLabel('Postal code')
            ->setAttributes([
                'class'    => 'form-control',
                'required' => true,
            ]);
        $this->add($element);

        $element = new Element\Select('country_id');
        $element->setLabel('Country')
            ->setValueOptions($this->countries)
            ->setAttributes(['class' => 'form-control']);
        $this->add($element);

        $element = new Element\Text('telephone');
        $element->setLabel('Telephone')
            ->setAttributes(['class' => 'form-control']);
        $this->add($element);

        $element = new Element\Submit('submit');
        $element->setAttributes(['class' => 'btn btn-primary'])
            ->setValue('Save');
        $this->add($element);
    }

    public function getInputFilterSpecification(): array
    {
        return [
            [
                'name'       => 'name',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern'  => '/^[a-z0-9_ \-,]{3,64}$/i',
                            'messages' => [
                                Validator\Regex::NOT_MATCH
                                    => 'Name can only container letters, numbers, and hyphens; must be 3-64 characters',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'street_address',
                'required'   => true,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern'  => '/^[a-z0-9_ \-,]{3,64}$/i',
                            'messages' => [
                                Validator\Regex::NOT_MATCH
                                    => 'Street address can only container letters, numbers, and hyphens; '
                                    . 'must be between 3 and 64 characters',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'street_address2',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern'  => '/^[a-z0-9_ \-,]{3,64}$/i',
                            'messages' => [
                                Validator\Regex::NOT_MATCH
                                    => 'Street address (2) can only container letters, numbers, and hyphens; '
                                    . 'must be between 3 and 64 characters',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'town',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern'  => '/^[a-z0-9_ \-,]{3,64}$/i',
                            'messages' => [
                                Validator\Regex::NOT_MATCH
                                    => 'Town can only container letters, numbers, and hyphens; '
                                    . 'must be between 3 and 64 characters',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'city',
                'required'   => true,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern'  => '/^[a-z0-9_ \-,]{3,64}$/i',
                            'messages' => [
                                Validator\Regex::NOT_MATCH
                                    => 'City can only container letters, numbers, and hyphens; '
                                    . 'must be between 3 and 64 characters',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'county',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern'  => '/^[a-z0-9_ \-,]{3,64}$/i',
                            'messages' => [
                                Validator\Regex::NOT_MATCH
                                    => 'County can only container letters, numbers, and hyphens; '
                                    . 'must be between 3 and 64 characters',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'country_id',
                'required'   => true,
                'filters'    => [
                    ['name' => Filter\ToInt::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\GreaterThan::class,
                        'options' => [
                            'min'       => 0,
                            'inclusive' => false,
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'postal_code',
                'required'   => true,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern'  => '/^[a-z0-9 ]{3,10}$/i',
                            'messages' => [
                                Validator\Regex::NOT_MATCH
                                    => 'Postal code can only container letters, numbers, and hyphens; '
                                    . 'must be between 3 and 10 characters',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'telephone',
                'required'   => true,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\Regex::class,
                        'options' => [
                            'pattern'  => '/^[a-z0-9 \+\(\)\.]{7,20}$/i',
                            'messages' => [
                                Validator\Regex::NOT_MATCH
                                    => 'Phone numbers can only contain digits 0-9, a space and ( )+. characters '
                                    . 'and can be a maximum of 20 characters',
                            ],
                        ],
                    ],
                ],
            ],
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
        ];
    }
}
