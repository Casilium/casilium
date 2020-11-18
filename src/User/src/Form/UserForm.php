<?php

namespace User\Form;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Form\Form;
use Laminas\InputFilter\ArrayInput;
use Laminas\Validator;
use Laminas\Validator\Hostname;
use Mezzio\Csrf\SessionCsrfGuard;
use User\Entity\User;
use User\Validator\UserExistsValidator;

/**
 * This form is used to collect user's email, full name, password and status. The form
 * can work in two scenarios - 'create' and 'update'. In 'create' scenario, user
 * enters password, in 'update' scenario he/she doesn't enter password.
 */
class UserForm extends Form
{
    /** @var SessionCsrfGuard */
    private $guard;

    /**
     * Entity manager.
     *
     * @var EntityManager
     */
    private $entityManager;

    /**
     * Scenario ('create' or 'update').
     *
     * @var string
     */
    private $scenario;

    /**
     * Current user.
     *
     * @var User
     */
    private $user;

    public function __construct(
        SessionCsrfGuard $guard,
        string $scenario = 'create',
        ?EntityManagerInterface $entityManager = null,
        ?User $user = null
    ) {
        // Define form name
        parent::__construct('user-form');

        // Set POST method for this form
        $this->setAttribute('method', 'post');

        // Save parameters for internal use.
        $this->scenario      = $scenario;
        $this->entityManager = $entityManager;
        $this->user          = $user;
        $this->guard         = $guard;

        $this->addElements();
        $this->addInputFilter();
    }

    /**
     * This method adds elements to form (input fields and submit button).
     */
    protected function addElements(): void
    {
        // Add "email" field
        $this->add([
            'type'       => 'text',
            'name'       => 'email',
            'attributes' => [
                'class' => 'form-control',
            ],
            'options'    => [
                'label' => 'E-mail',
            ],
        ]);

        // Add "full_name" field
        $this->add([
            'type'       => 'text',
            'name'       => 'full_name',
            'attributes' => [
                'class' => 'form-control',
            ],
            'options'    => [
                'label' => 'Full Name',
            ],
        ]);

        if ($this->scenario == 'create') {
            // Add "password" field
            $this->add([
                'type'       => 'password',
                'name'       => 'password',
                'attributes' => [
                    'id'    => 'password',
                    'class' => 'form-control',
                ],
                'options'    => [
                    'label' => 'Password',
                ],
            ]);

            // Add "confirm_password" field
            $this->add([
                'type'       => 'password',
                'name'       => 'confirm_password',
                'attributes' => [
                    'id'    => 'confirm_password',
                    'class' => 'form-control',
                ],
                'options'    => [
                    'label' => 'Confirm password',
                ],
            ]);
        }

        // Add "status" field
        $this->add([
            'type'       => 'select',
            'name'       => 'status',
            'attributes' => [
                'class' => 'form-control',
            ],
            'options'    => [
                'label'         => 'Status',
                'value_options' => [
                    1 => 'Active',
                    2 => 'Retired',
                ],
            ],
        ]);

        // Add "roles" field
        $this->add([
            'type'       => 'select',
            'name'       => 'roles',
            'attributes' => [
                'class'    => 'form-control',
                'multiple' => 'multiple',
            ],
            'options'    => [
                'label' => 'Role(s)',
            ],
        ]);

        $this->add([
            'type'    => 'checkbox',
            'name'    => 'mfa_enabled',
            'options' => [
                'label'              => 'MFA Enabled',
                'checked_value'      => 1,
                'unchecked_value'    => 0,
                'use_hidden_element' => true,
            ],
        ]);

        // Add the CSRF field
        $this->add([
            'type' => 'hidden',
            'name' => 'csrf',
        ]);

        // Add the Submit button
        $this->add([
            'type'       => 'submit',
            'name'       => 'submit',
            'attributes' => [
                'class' => 'btn btn-primary',
                'value' => 'Create',
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
                    'name'    => 'StringLength',
                    'options' => [
                        'min' => 1,
                        'max' => 128,
                    ],
                ],
                [
                    'name'    => 'EmailAddress',
                    'options' => [
                        'allow'      => Hostname::ALLOW_DNS,
                        'useMxCheck' => false,
                    ],
                ],
                [
                    'name'    => UserExistsValidator::class,
                    'options' => [
                        'entityManager' => $this->entityManager,
                        'user'          => $this->user,
                    ],
                ],
            ],
        ]);

        // Add input for "full_name" field
        $inputFilter->add([
            'name'       => 'full_name',
            'required'   => true,
            'filters'    => [
                ['name' => 'StringTrim'],
            ],
            'validators' => [
                [
                    'name'    => 'StringLength',
                    'options' => [
                        'min' => 1,
                        'max' => 512,
                    ],
                ],
            ],
        ]);

        $inputFilter->add([
            'name'       => 'mfa_enabled',
            'required'   => true,
            'validators' => [
                [
                    'name'    => Validator\InArray::class,
                    'options' => [
                        'haystack' => [true, false],
                    ],
                ],
            ],
        ]);

        $inputFilter->add([
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
        ]);

        if ($this->scenario == 'create') {
            // Add input for "password" field
            $inputFilter->add([
                'name'       => 'password',
                'required'   => true,
                'filters'    => [],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 6,
                            'max' => 64,
                        ],
                    ],
                ],
            ]);

            // Add input for "confirm_password" field
            $inputFilter->add([
                'name'       => 'confirm_password',
                'required'   => true,
                'filters'    => [],
                'validators' => [
                    [
                        'name'    => 'Identical',
                        'options' => [
                            'token' => 'password',
                        ],
                    ],
                ],
            ]);
        }

        // Add input for "status" field
        $inputFilter->add([
            'name'       => 'status',
            'required'   => true,
            'filters'    => [
                ['name' => 'ToInt'],
            ],
            'validators' => [
                ['name' => 'InArray', 'options' => ['haystack' => [1, 2]]],
            ],
        ]);

        // Add input for "roles" field
        $inputFilter->add([
            'class'      => ArrayInput::class,
            'name'       => 'roles',
            'required'   => true,
            'filters'    => [
                ['name' => 'ToInt'],
            ],
            'validators' => [
                ['name' => 'GreaterThan', 'options' => ['min' => 0]],
            ],
        ]);
    }
}
