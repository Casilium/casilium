<?php

declare(strict_types=1);

namespace Ticket\Form;

use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;

class AssignQueueMembersForm extends Form implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('queue-members');

        $this->addFormElements();
    }

    public function addFormElements(): void
    {
        $element = new Element\Select('members');
        $element->setLabel('Queue Members');
        $element->setAttributes([
            'id'       => 'members',
            'class'    => 'form-control',
            'multiple' => true,
        ]);
        $element->setOptions([
            'disable_in_array_validator' => true,
        ]);
        $this->add($element);

        $element = new Element\Submit('submit');
        $element->setAttributes([
            'class' => 'btn btn-primary',
            'id'    => 'submit',
            'value' => 'Submit',
        ]);
        $this->add($element);
    }

    public function getInputFilterSpecification(): array
    {
        return [
            [
                'name'     => 'members',
                'required' => true,
            ],
        ];
    }
}
