<?php

declare(strict_types=1);

namespace User\View\Helper\Factory;

use interop\container\containerinterface;
use User\View\Helper\GetUserNameFromId;

class GetUserNameFromIdFactory
{
    public function __invoke(containerinterface $container): GetUserNameFromId
    {
        $entityManager = $container->get('doctrine.entity_manager.orm_default');
        return new GetUserNameFromId($entityManager);
    }
}
