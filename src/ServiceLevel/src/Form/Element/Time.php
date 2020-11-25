<?php

declare(strict_types=1);

namespace ServiceLevel\Form\Element;

use Laminas\Form\Element;

class Time extends Element\Time
{
    /**
     * @inheritDoc
     */
    public function __construct($name = null, $options = [])
    {
        $this->setAttributes([
            'min'   => '00:00',
            'max'   => '23:59',
            'class' => 'form-control',
        ]);

        $this->setOptions([
            'format' => 'H:i',
        ]);

        parent::__construct($name, $options);
    }
}
