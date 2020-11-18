<?php
declare(strict_types=1);

namespace User\Handler\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Interop\Container\ContainerInterface;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\LazyListener;
use Mezzio\Template\TemplateRendererInterface;
use User\EventListener\UserEventListener;
use User\Handler;

class ViewUserPageHandlerFactory
{
    public function __invoke(ContainerInterface $container): Handler\ViewUserPageHandler
    {
        $events = new EventManager();
        $events->setIdentifiers([Handler\ViewUserPageHandler::class]);

        $lazyListener = new LazyListener([
            'listener' => UserEventListener::class,
            'method'   => 'onViewUser',
        ], $container);

        $events->attach('user.view', $lazyListener);

        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine.entity_manager.orm_default');

        /** @var TemplateRendererInterface $renderer */
        $renderer = $container->get(TemplateRendererInterface::class);

        return new Handler\ViewUserPageHandler($em, $renderer, $events);
    }
}
