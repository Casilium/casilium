<?php
declare(strict_types=1);

namespace User\View\Helper\Factory;

use Interop\Container\ContainerInterface;
use User\View\Helper\GetUserNameFromId;

class GetUserNameFromIdFactory
{
    public function __invoke(ContainerInterface $container): GetUserNameFromId
    {
        $entityManager = $container->get('doctrine.entity_manager.orm_default');
        return new GetUserNameFromId($entityManager);
    }
}
