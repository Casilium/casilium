<?php

declare(strict_types=1);

namespace UserAuthentication\Form;

use Laminas\Filter;
use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Mezzio\Csrf\SessionCsrfGuard;

class LoginForm extends Form implements InputFilterProviderInterface
{
    /**
     * @var SessionCsrfGuard
     */
    private $guard;

    public function __construct(SessionCsrfGuard $guard)
    {
        parent::__construct('login-form');
        $this->guard = $guard;

        $this->addElements();;
    }

    /**
     * Add form elements
     */
    public function addElements() : void
    {
        $element = new Element\Text('username');
        $element->setLabel('Username')
            ->setAttributes([
                'id' => 'username',
                'class' => 'form-control',
                'placeholder' => 'Username',
                'autocomplete' => 'off',
            ]);
        $this->add($element);

        $element = new Element\Password('password');
        $element->setLabel('Password')
            ->setAttributes([
                'id' => 'password',
                'class' => 'form-control',
                'placeholder' => 'Password',
                'autocomplete' => 'off',
            ]);
        $this->add($element);

        $element = new Element\Hidden('csrf');
        $this->add($element);

        $element = new Element\Submit('submit');
        $element->setValue('Login')
            ->setAttribute('class', 'btn btn-primary');
        $this->add($element);
    }

    /**
     * Define input filter specification
     * @return array[]
     */
    public function getInputFilterSpecification() : array
    {
        return [
            [
                'name' => 'username',
                'required' => true,
                'filters' => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
            ],
            [
                'name' => 'password',
                'required' => true,
                'filters' => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
            ],
            [
                'name' => 'csrf',
                'required' => true,
                'validators' => [
                    [
                        'name' => 'callback',
                        'options' => [
                            'callback' => function ($value) {
                                return $this->guard->validateToken($value);
                            },
                            'messages' => [
                                'callbackValue' => 'The form submitted did not originate from the expected site'
                            ],
                        ],
                    ]
                ],
            ],
        ];
    }
}