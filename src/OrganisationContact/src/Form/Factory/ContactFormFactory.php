<?php

declare(strict_types=1);

namespace OrganisationContact\Form\Factory;

use OrganisationContact\Form\ContactForm;
use OrganisationContact\Hydrator\ContactHydrator;
use Psr\Container\ContainerInterface;

class ContactFormFactory
{
    public function __invoke(ContainerInterface $container): ContactForm
    {
        $hydrator = $container->get(ContactHydrator::class);
        return new ContactForm($hydrator);
    }
}
