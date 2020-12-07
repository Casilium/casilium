<?php

declare(strict_types=1);

namespace App\Middleware;

use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PrgMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var SessionInterface $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        // check if request method is post
        if ($request->getMethod() === 'POST') {
            // save post data into session, then redirect to current page with status code 303
            $session->set('post_data', $request->getParsedBody());
            return new RedirectResponse($request->getUri(), 303);
        }

        // if session has post data
        if ($session->has('post_data')) {
            // get post data and unset session key
            $post = $session->get('post_data');
            $session->unset('post_data');

            $request = $request->withMethod('POST');
            $request = $request->withParsedBody($post);
        }

        return $handler->handle($request);
    }
}
