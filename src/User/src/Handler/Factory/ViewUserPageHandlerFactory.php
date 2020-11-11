<?php
declare(strict_types=1);

namespace User\Handler\Factory;

use Interop\Container\ContainerInterface;
use User\EventListener\UserEventListener;
use User\Handler;
use Mezzio\Template\TemplateRendererInterface;

use Laminas\EventManager\EventManager;
use Laminas\EventManager\LazyListener;

class ViewUserPageHandlerFactory
{
    public function __invoke(ContainerInterface $container): Handler\ViewUserPageHandler
    {
        $events = new EventManager();
        $events->setIdentifiers([Handler\ViewUserPageHandler::class]);

        $lazyListener = new LazyListener([
            'listener' => UserEventListener::class,
            'method' => 'onViewUser',
        ], $container);

        $events->attach('user.view', $lazyListener);

        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = $container->get('doctrine.entity_manager.orm_default');

        /** @var TemplateRendererInterface $renderer */
        $renderer = $container->get(TemplateRendererInterface::class);

        return new Handler\ViewUserPageHandler($em, $renderer, $events);
    }
}
