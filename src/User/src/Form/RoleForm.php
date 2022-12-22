<?php

namespace User\Form;

use Doctrine\ORM\EntityManagerInterface;
use Laminas\Form\Form;
use Mezzio\Csrf\SessionCsrfGuard;
use User\Entity\Role;
use User\Validator\RoleExistsValidator;

/**
 * The form for collecting information about Role.
 */
class RoleForm extends Form
{
    /** @var EntityManagerInterface|null */
    private $entityManager;

    /** @var SessionCsrfGuard $guard */
    private $guard;

    /** @var Role|null */
    private $role;

    public function __construct(
        SessionCsrfGuard $guard,
        ?EntityManagerInterface $entityManager = null,
        ?Role $role = null
    ) {
        $this->guard         = $guard;
        $this->entityManager = $entityManager;
        $this->role          = $role;

        // Define form name
        parent::__construct('role-form');

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
        // Add "name" field
        $this->add([
            'type'       => 'text',
            'name'       => 'name',
            'attributes' => [
                'class' => 'form-control',
                'id'    => 'name',
            ],
            'options'    => [
                'label' => 'Role Name',
            ],
        ]);

        // Add "description" field
        $this->add([
            'type'       => 'textarea',
            'name'       => 'description',
            'attributes' => [
                'class' => 'form-control',
                'id'    => 'description',
            ],
            'options'    => [
                'label' => 'Description',
            ],
        ]);

        // Add "inherit_roles" field
        $this->add([
            'type'       => 'select',
            'name'       => 'inherit_roles',
            'attributes' => [
                'class'    => 'form-control',
                'id'       => 'inherit_roles',
                'multiple' => 'multiple',
            ],
            'options'    => [
                'label' => 'Optionally inherit permissions from these role(s)',
            ],
        ]);

        // Add the Submit button
        $this->add([
            'type'       => 'submit',
            'name'       => 'submit',
            'attributes' => [
                'class' => 'btn btn-primary',
                'value' => 'Create',
                'id'    => 'submit',
            ],
        ]);

        // Add the CSRF field
        $this->add([
            'type' => 'hidden',
            'name' => 'csrf',
        ]);
    }

    /**
     * This method creates input filter (used for form filtering/validation).
     */
    private function addInputFilter(): void
    {
        // Create input filter
        $inputFilter = $this->getInputFilter();

        // Add input for "name" field
        $inputFilter->add([
            'name'       => 'name',
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
                    'name'    => RoleExistsValidator::class,
                    'options' => [
                        'entityManager' => $this->entityManager,
                        'role'          => $this->role,
                    ],
                ],
            ],
        ]);

        // Add input for "description" field
        $inputFilter->add([
            'name'       => 'description',
            'required'   => true,
            'filters'    => [
                ['name' => 'StringTrim'],
            ],
            'validators' => [
                [
                    'name'    => 'StringLength',
                    'options' => [
                        'min' => 0,
                        'max' => 1024,
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

        // Add input for "inherit_roles" field
        $inputFilter->add([
            'name'       => 'inherit_roles',
            'required'   => false,
            'filters'    => [],
            'validators' => [],
        ]);
    }
}
