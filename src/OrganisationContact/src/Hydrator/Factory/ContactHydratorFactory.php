<?php

declare(strict_types=1);

namespace OrganisationContact\Hydrator\Factory;

use OrganisationContact\Hydrator\ContactHydrator;
use OrganisationContact\Service\ContactService;
use Psr\Container\ContainerInterface;

class ContactHydratorFactory
{
    public function __invoke(ContainerInterface $container): ContactHydrator
    {
        $contactService = $container->get(ContactService::class);
        return new ContactHydrator($contactService);
    }
}
