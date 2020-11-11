<?php

declare(strict_types=1);

namespace User\EventListener;

use Interop\Container\ContainerInterface;
use Laminas\EventManager\Event;

class UserEventListener
{
    public function __invoke(ContainerInterface $container)
    {
        // Grab some dependencies from the container and return self
        return new self();
    }

    public function onViewUser(Event $event)
    {
        $name = $event->getName();
        $target = $event->getTarget();
        $params = $event->getParams();
    }
}