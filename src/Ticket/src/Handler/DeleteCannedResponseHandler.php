<?php

declare(strict_types=1);

namespace Ticket\Handler;

use App\Traits\CsrfTrait;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Csrf\CsrfMiddleware;
use Mezzio\Csrf\SessionCsrfGuard;
use Mezzio\Helper\UrlHelper;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ticket\Form\CannedResponseDeleteForm;
use Ticket\Service\CannedResponseManager;

class DeleteCannedResponseHandler implements RequestHandlerInterface
{
    use CsrfTrait;

    public function __construct(
        private readonly CannedResponseManager $cannedResponseManager,
        private readonly TemplateRendererInterface $renderer,
        private readonly UrlHelper $urlHelper
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id             = (int) $request->getAttribute('id', 0);
        $cannedResponse = $id > 0 ? $this->cannedResponseManager->findById($id) : null;
        if ($cannedResponse === null) {
            return new HtmlResponse($this->renderer->render('error::404'), 404);
        }

        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        /** @var SessionCsrfGuard $guard */
        $guard = $request->getAttribute(CsrfMiddleware::GUARD_ATTRIBUTE);
        $token = $this->getToken($session, $guard);
        $form  = new CannedResponseDeleteForm($guard);

        if ($request->getMethod() === 'POST') {
            $form->setData($request->getParsedBody());
            if ($form->isValid()) {
                $this->cannedResponseManager->delete($cannedResponse);
                return new RedirectResponse(
                    $this->urlHelper->generate('admin.canned_response.list')
                );
            }
        }

        return new HtmlResponse($this->renderer->render('ticket::delete-canned-response', [
            'cannedResponse' => $cannedResponse,
            'form'           => $form,
            'token'          => $token,
        ]));
    }
}
