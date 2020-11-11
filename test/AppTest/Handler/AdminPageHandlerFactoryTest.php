<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\AdminPageHandler;
use App\Handler\Factory\AdminPageHandlerFactory;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;

class AdminPageHandlerFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testSetupFactory(): void
    {
        $this->container->has(TemplateRendererInterface::class)
            ->willReturn(true);

        $this->container->get(TemplateRendererInterface::class)
            ->willReturn($this->prophesize(TemplateRendererInterface::class));

        $factory = new AdminPageHandlerFactory();
        $adminPage = $factory($this->container->reveal());

        $this->assertInstanceOf(AdminPageHandler::class, $adminPage);
    }
}