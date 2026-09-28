<?php

declare(strict_types=1);

namespace Ticket\Form;

use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\Validator;
use Mezzio\Csrf\SessionCsrfGuard;

class CannedResponseDeleteForm extends Form
{
    public function __construct(private readonly SessionCsrfGuard $guard)
    {
        parent::__construct('canned-response-delete-form');
        $this->setAttribute('method', 'post');

        $this->add(new Element\Hidden('csrf'));

        $submit = new Element\Submit('submit');
        $submit->setValue('delete')
            ->setLabel('Delete')
            ->setAttribute('class', 'btn btn-danger');
        $this->add($submit);

        $this->addInputFilter();
    }

    private function addInputFilter(): void
    {
        $inputFilter = $this->getInputFilter();
        $inputFilter->add([
            'name'       => 'csrf',
            'required'   => true,
            'validators' => [
                [
                    'name'    => Validator\Callback::class,
                    'options' => [
                        'callback' => fn (mixed $value): bool => $this->guard->validateToken((string) $value),
                        'messages' => [
                            Validator\Callback::INVALID_VALUE
                                => 'The form submission did not originate from the expected site',
                        ],
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name'       => 'submit',
            'required'   => true,
            'validators' => [
                [
                    'name'    => Validator\Identical::class,
                    'options' => ['token' => 'delete'],
                ],
            ],
        ]);
    }
}
