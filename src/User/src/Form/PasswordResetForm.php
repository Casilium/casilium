<?php

declare(strict_types=1);

namespace User\Form;

use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\Validator\Hostname;

/**
 * This form is used to collect user's E-mail address (used to recover password).
 */
class PasswordResetForm extends Form
{
    public function __construct()
    {
        // Define form name
        parent::__construct('password-reset-form');

        // Set POST method for this form
        $this->setAttribute('method', 'post');

        $this->addElements();
        $this->addInputFilter();
    }

    /**
     * This method adds elements to form (input fields and submit button).
     */
    protected function addElements(): void
    {
        $element = new Element\Email('email');
        $element->setLabel('E-Mail')
            ->setAttributes([
                'id'          => 'email',
                'class'       => 'form-control',
                'placeholder' => 'email',
            ]);
        $this->add($element);

        // Add the CAPTCHA field
        $this->add([
            'type'    => 'captcha',
            'name'    => 'captcha',
            'options' => [
                'label'   => 'Human check',
                'captcha' => [
                    'class'          => 'Image',
                    'imgDir'         => 'public/img/captcha',
                    'suffix'         => '.png',
                    'imgUrl'         => '/img/captcha/',
                    'imgAlt'         => 'CAPTCHA Image',
                    'font'           => './data/font/thorne_shaded.ttf',
                    'fsize'          => 24,
                    'width'          => 350,
                    'height'         => 100,
                    'expiration'     => 600,
                    'dotNoiseLevel'  => 40,
                    'lineNoiseLevel' => 3,
                ],
            ],
        ]);

        // Add the CSRF field
        $this->add([
            'type'    => 'csrf',
            'name'    => 'csrf',
            'options' => [
                'csrf_options' => [
                    'timeout' => 600,
                ],
            ],
        ]);

        // Add the Submit button
        $this->add([
            'type'       => 'submit',
            'name'       => 'submit',
            'attributes' => [
                'value' => 'Reset Password',
                'id'    => 'submit',
            ],
        ]);
    }

    /**
     * This method creates input filter (used for form filtering/validation).
     */
    private function addInputFilter(): void
    {
        // Create main input filter
        $inputFilter = $this->getInputFilter();

        // Add input for "email" field
        $inputFilter->add([
            'name'       => 'email',
            'required'   => true,
            'filters'    => [
                ['name' => 'StringTrim'],
            ],
            'validators' => [
                [
                    'name'    => 'EmailAddress',
                    'options' => [
                        'allow'      => Hostname::ALLOW_DNS,
                        'useMxCheck' => false,
                    ],
                ],
            ],
        ]);
    }
}
