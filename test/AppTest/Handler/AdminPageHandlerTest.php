<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\AdminPageHandler;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminPageHandlerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testHtmlResponse(): void
    {
        $renderer = $this->prophesize(TemplateRendererInterface::class);
        $renderer->render('app::admin-page', Argument::type('array'))
            ->willReturn('');

        $adminPage = new AdminPageHandler($renderer->reveal());
        $response  = $adminPage->handle($this->prophesize(ServerRequestInterface::class)->reveal());

        self::assertInstanceOf(HtmlResponse::class, $response);
    }
}
