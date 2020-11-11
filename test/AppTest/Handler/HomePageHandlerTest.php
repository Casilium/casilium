<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\HomePageHandler;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function get_class;

class HomePageHandlerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ContainerInterface */
    protected $container;

    /** @var TemplateRendererInterface */
    protected $renderer;

    protected function setUp(): void
    {
        $this->renderer  = $this->prophesize(TemplateRendererInterface::class);
        $this->container = $this->prophesize(ContainerInterface::class);
    }


    public function testReturnsHtmlResponse()
    {
        $this->renderer
            ->render('app::home-page', Argument::type('array'))
            ->willReturn('');

        $homePage = new HomePageHandler($this->renderer->reveal());

        $request = $this->prophesize(ServerRequestInterface::class);

        $response = $homePage->handle($request->reveal());

        $this->assertInstanceOf(HtmlResponse::class, $response);
    }
}
