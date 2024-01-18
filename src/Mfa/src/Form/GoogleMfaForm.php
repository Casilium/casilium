<?php

declare(strict_types=1);

namespace Mfa\Form;

use Laminas\Filter;
use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator;
use Mezzio\Csrf\SessionCsrfGuard;

/**
 * GoogleMfaForm - Form for displaying QR code
 */
class GoogleMfaForm extends Form implements InputFilterProviderInterface
{
    /** @var SessionCsrfGuard */
    private $guard;

    public function __construct(SessionCsrfGuard $guard)
    {
        $this->guard = $guard;

        parent::__construct('google-mfa-form');
        $this->addElements();
    }

    /**
     * Add the form elements to the form
     */
    public function addElements(): void
    {
        $element = (new Element\Text('pin'))
            ->setLabel('Enter verification code')
            ->setAttributes([
                'id'           => 'totpPin',
                'class'        => 'form-control',
                'autocomplete' => 'off',
                'placeholder'  => 'Enter 6-digit code',
                'autofocus'    => true,
                'required'     => true,
            ]);
        $this->add($element);

        $element = new Element\Hidden();
        $element->setName('secret_key');
        $this->add($element);

        $element = new Element\Hidden('csrf');
        $this->add($element);

        $element = (new Element\Submit('submit'))
            ->setValue('Verify')
            ->setAttributes([
                'class' => 'btn btn-primary',
            ]);
        $this->add($element);
    }

    /**
     * Define input filter/validation
     *
     * @return array
     */
    public function getInputFilterSpecification(): array
    {
        return [
            [
                'name'       => 'pin',
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
                    ['name' => Validator\Digits::class],
                ],
            ],
            [
                'name'       => 'csrf',
                'required'   => true,
                'validators' => [
                    [
                        'name'    => 'callback',
                        'options' => [
                            'callback' => function ($value) {
                                return $this->guard->validateToken($value);
                            },
                            'messages' => [
                                'callbackValue' => 'The form submitted did not originate from the expected site',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
