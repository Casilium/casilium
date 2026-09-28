<?php

declare(strict_types=1);

namespace Ticket\Form;

use Doctrine\ORM\EntityManagerInterface;
use Laminas\Filter;
use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\Validator;
use Mezzio\Csrf\SessionCsrfGuard;
use Ticket\Entity\CannedResponse;
use Ticket\Validator\CannedResponseTitleAvailableValidator;

class CannedResponseForm extends Form
{
    public function __construct(
        private readonly SessionCsrfGuard $guard,
        EntityManagerInterface $entityManager,
        ?CannedResponse $cannedResponse = null
    ) {
        parent::__construct('canned-response-form');
        $this->setAttribute('method', 'post');

        $this->addElements($cannedResponse !== null);
        $this->addInputFilter($entityManager, $cannedResponse);
    }

    private function addElements(bool $isEditing): void
    {
        $this->add(new Element\Hidden('id'));

        $title = new Element\Text('title');
        $title->setLabel('Title')->setAttributes([
            'class' => 'form-control',
            'id'    => 'title',
        ]);
        $this->add($title);

        $response = new Element\Textarea('response');
        $response->setLabel('Response')->setAttributes([
            'class' => 'form-control',
            'id'    => 'response',
            'rows'  => 10,
        ]);
        $this->add($response);

        $this->add(new Element\Hidden('csrf'));

        $submit = new Element\Submit('submit');
        $submit->setValue($isEditing ? 'Save Changes' : 'Create Response')
            ->setAttribute('class', 'btn btn-primary');
        $this->add($submit);
    }

    private function addInputFilter(
        EntityManagerInterface $entityManager,
        ?CannedResponse $cannedResponse
    ): void {
        $inputFilter = $this->getInputFilter();

        $inputFilter->add([
            'name'       => 'id',
            'required'   => false,
            'filters'    => [
                ['name' => Filter\StringTrim::class],
            ],
            'validators' => [
                ['name' => Validator\Digits::class],
            ],
        ]);

        $inputFilter->add([
            'name'       => 'title',
            'required'   => true,
            'filters'    => [
                ['name' => Filter\StringTrim::class],
                ['name' => Filter\StripTags::class],
            ],
            'validators' => [
                [
                    'name'    => Validator\StringLength::class,
                    'options' => ['min' => 1, 'max' => 128],
                ],
                [
                    'name'    => CannedResponseTitleAvailableValidator::class,
                    'options' => [
                        'entityManager'  => $entityManager,
                        'cannedResponse' => $cannedResponse,
                    ],
                ],
            ],
        ]);

        $inputFilter->add([
            'name'       => 'response',
            'required'   => true,
            'filters'    => [
                ['name' => Filter\StringTrim::class],
                ['name' => Filter\StripTags::class],
            ],
            'validators' => [
                [
                    'name'    => Validator\StringLength::class,
                    'options' => ['min' => 1],
                ],
            ],
        ]);

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
    }
}
