<?php

declare(strict_types=1);

namespace User\Form;

use Doctrine\ORM\EntityManagerInterface;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use Mezzio\Csrf\SessionCsrfGuard;

/**
 * The form for collecting information about permissions assigned to a role.
 */
class RolePermissionsForm extends Form
{
    /** @var SessionCsrfGuard */
    protected $guard;

    /** @var EntityManagerInterface */
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager, SessionCsrfGuard $guard)
    {
        $this->entityManager = $entityManager;
        $this->guard         = $guard;

        // Define form name
        parent::__construct('role-permissions-form');

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
        // Add a fieldset for permissions
        $fieldset = new Fieldset('permissions');
        $this->add($fieldset);

        // Add the Submit button
        $this->add([
            'type'       => 'submit',
            'name'       => 'submit',
            'attributes' => [
                'class' => 'btn btn-primary',
                'value' => 'Save',
                'id'    => 'submit',
            ],
        ]);

        // Add the CSRF field
        $this->add([
            'type' => 'hidden',
            'name' => 'csrf',
        ]);
    }

    public function addPermissionField(string $name, string $label, bool $isDisabled = false): void
    {
        // Add a permission field
        $this->get('permissions')->add([
            'type'       => 'checkbox',
            'name'       => $name,
            'attributes' => [
                'class'    => 'form-control',
                'id'       => $name,
                'disabled' => $isDisabled,
            ],
            'options'    => [
                'label' => $label,
            ],
        ]);

        // Add input
        $this->getInputFilter()->get('permissions')->add([
            'name'       => $name,
            'required'   => false,
            'filters'    => [],
            'validators' => [
                ['name' => 'IsInt'],
            ],
        ]);
    }

    /**
     * This method creates input filter (used for form filtering/validation).
     */
    private function addInputFilter(): void
    {
        // Create input filter
        $inputFilter = $this->getInputFilter();

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
