<?php

declare(strict_types=1);

namespace User\Form;

use Doctrine\ORM\EntityManagerInterface;
use Laminas\Form\Form;
use Mezzio\Csrf\SessionCsrfGuard;
use User\Entity\Permission;
use User\Validator\PermissionExistsValidator;

/**
 * The form for collecting information about Permission.
 */
class PermissionForm extends Form
{
    /** @var SessionCsrfGuard */
    protected $guard;

    /** @var EntityManagerInterface|null */
    protected $entityManager;

    /** @var Permission|null */
    protected $permission;

    /** @var string */
    protected $scenario;

    public function __construct(
        SessionCsrfGuard $guard,
        string $scenario = 'create',
        ?EntityManagerInterface $entityManager = null,
        ?Permission $permission = null
    ) {
        $this->guard         = $guard;
        $this->scenario      = $scenario;
        $this->entityManager = $entityManager;
        $this->permission    = $permission;

        // Define form name
        parent::__construct('permission-form');

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
                'class'       => 'form-control',
                'id'          => 'name',
                'placeholder' => 'Enter name',
            ],
            'options'    => [
                'label' => 'Permission Name',
            ],
        ]);

        // Add "description" field
        $this->add([
            'type'       => 'textarea',
            'name'       => 'description',
            'attributes' => [
                'class'       => 'form-control',
                'id'          => 'description',
                'placeholder' => 'Enter description',
            ],
            'options'    => [
                'label' => 'Description',
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
                    'name'    => PermissionExistsValidator::class,
                    'options' => [
                        'entityManager' => $this->entityManager,
                        'permission'    => $this->permission,
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
                        'min' => 1,
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
    }
}
