<?php

declare(strict_types=1);

namespace Ticket\Handler;

use App\Traits\CsrfTrait;
use Doctrine\ORM\EntityManagerInterface;
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
use Ticket\Entity\CannedResponse;
use Ticket\Form\CannedResponseForm;
use Ticket\Service\CannedResponseManager;

use function is_array;

class EditCannedResponseHandler implements RequestHandlerInterface
{
    use CsrfTrait;

    public function __construct(
        private readonly CannedResponseManager $cannedResponseManager,
        private readonly EntityManagerInterface $entityManager,
        private readonly TemplateRendererInterface $renderer,
        private readonly UrlHelper $urlHelper
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $cannedResponse = $this->findCannedResponse($request);
        if ($request->getAttribute('id') !== null && $cannedResponse === null) {
            return new HtmlResponse($this->renderer->render('error::404'), 404);
        }

        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        /** @var SessionCsrfGuard $guard */
        $guard = $request->getAttribute(CsrfMiddleware::GUARD_ATTRIBUTE);
        $token = $this->getToken($session, $guard);

        $form = new CannedResponseForm($guard, $this->entityManager, $cannedResponse);
        if ($request->getMethod() === 'POST') {
            $form->setData($request->getParsedBody());
            if ($form->isValid()) {
                $data = $form->getData();
                if (is_array($data)) {
                    $data['id'] = $cannedResponse?->getId();
                    $this->cannedResponseManager->save($data);
                    return new RedirectResponse(
                        $this->urlHelper->generate('admin.canned_response.list')
                    );
                }
            }
        } elseif ($cannedResponse !== null) {
            $form->setData([
                'id'       => $cannedResponse->getId(),
                'title'    => $cannedResponse->getTitle(),
                'response' => $cannedResponse->getResponse(),
            ]);
        }

        return new HtmlResponse($this->renderer->render('ticket::edit-canned-response', [
            'cannedResponse' => $cannedResponse,
            'form'           => $form,
            'token'          => $token,
        ]));
    }

    private function findCannedResponse(ServerRequestInterface $request): ?CannedResponse
    {
        $id = $request->getAttribute('id');
        if ($id === null) {
            return null;
        }

        return $this->cannedResponseManager->findById((int) $id);
    }
}
