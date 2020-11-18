<?php

declare(strict_types=1);

namespace App\Middleware;

use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function in_array;

class XMLHttpRequestTemplateMiddleware implements MiddlewareInterface
{
    /** @var TemplateRendererInterface */
    private $renderer;

    public function __construct(TemplateRendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // set template layout to false to disable layout whe n request has X-Requested-With =
        // XmlHttpRequest as an ajax detection
        if (in_array('XMLHttpRequest', $request->getHeader('X-Requested-With'), true)) {
            (function ($template) {
                $template->layout = false;
            })->bindTo($this->renderer, $this->renderer)($this->renderer);
        }

        return $handler->handle($request);
    }
}
