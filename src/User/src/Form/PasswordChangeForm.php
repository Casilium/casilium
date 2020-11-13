<?php
namespace User\Form;

use Laminas\Filter;
use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\Validator;
use Laminas\InputFilter\InputFilterProviderInterface;

/**
 * This form is used when changing user's password (to collect user's old password
 * and new password) or when resetting user's password (when user forgot his password).
 */
class PasswordChangeForm extends Form implements InputFilterProviderInterface
{
    /**
     * There can be two scenarios - 'change' or 'reset'.
     * @var string
     */
    private $scenario;

    /**
     * Constructor.
     * @param string $scenario Either 'change' or 'reset'.
     */
    public function __construct(string $scenario = 'change')
    {
        // Define form name
        parent::__construct('password-change-form');

        $this->scenario = $scenario;

        // Set POST method for this form
        $this->setAttribute('method', 'post');

        $this->addElements();
    }

    /**
     * This method adds elements to form (input fields and submit button).
     */
    protected function addElements(): void
    {
        // If scenario is 'change', we do not ask for old password.
        if ($this->scenario === 'change') {

            // add current password field
            $element = new Element\Password('current_password');
            $element->setLabel('Current password');
            $element->setAttributes([
                'autocomplete' => 'off',
                'class' => 'form-control',
                'id' => 'current_password',
                'placeholder' => 'current password'
            ]);
            $this->add($element);
        }

        // Add "new_password" field
        $element = new Element\Password('new_password');
        $element->setLabel('New password');
        $element->setAttributes([
            'autocomplete' => 'off',
            'class' => 'form-control',
            'id' => 'new_password',
            'placeholder' => 'new password'
        ]);
        $this->add($element);

        // Add "new_password" field
        $element = new Element\Password('confirm_new_password');
        $element->setLabel('confirm new password');
        $element->setAttributes([
            'autocomplete' => 'off',
            'class' => 'form-control',
            'id' => 'confirm_new_password',
            'placeholder' => 'Confirm you password'
        ]);
        $this->add($element);

        // Add the Submit button
        $element = new Element\Submit('submit');
        $element->setLabel('submit')
            ->setValue('Change password')
            ->setAttributes([
                'id' => 'submit',
                'class' => 'btn btn-primary'
            ]);
        $this->add($element);
    }

    /**
     * This method creates input filter (used for form filtering/validation).
     */
    public function getInputFilterSpecification() : array
    {
        return [
            [
                'name' => 'current_password',
                'required' => true,
                'validators' => [
                    [
                        'name'    => Validator\StringLength::class,
                        'options' => [
                            'min' => 6,
                            'max' => 64
                        ],
                    ],
                ],
            ],
            [
                'name'     => 'new_password',
                'required' => true,
                'validators' => [
                    [
                        'name'    => Validator\StringLength::class,
                        'options' => [
                            'min' => 6,
                            'max' => 64
                        ],
                    ],
                ],
            ],
            [
                'name'     => 'confirm_new_password',
                'required' => true,
                'filters'  => [
                ],
                'validators' => [
                    [
                        'name'    => Validator\Identical::class,
                        'options' => [
                            'token' => 'new_password',
                            'messages' => [
                                Validator\Identical::NOT_SAME => 'Passwords do not match'
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
