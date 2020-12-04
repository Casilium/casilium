<?php

declare(strict_types=1);

namespace Organisation\Handler;

use Exception;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Organisation\Form\SelectForm;
use Organisation\Service\OrganisationManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class OrganisationSelectHandler implements RequestHandlerInterface
{
    /** @var RouterInterface */
    protected $router;

    /** @var OrganisationManager */
    protected $organisationManager;

    /** @var TemplateRendererInterface */
    protected $renderer;

    public function __construct(
        RouterInterface $router,
        OrganisationManager $manager,
        TemplateRendererInterface $renderer
    ) {
        $this->router              = $router;
        $this->organisationManager = $manager;
        $this->renderer            = $renderer;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $query = $request->getQueryParams()['q'] ?? null;
        $route = $request->getQueryParams()['returnTo'] ?? null;

        if ($query !== null) {
            $result = $this->organisationManager->autoCompleteName($query);
            return new JsonResponse($result);
        }

        $form = new SelectForm();
        if ($request->getMethod() === 'POST') {
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                $data = $form->getData();

                $organisationId = $data['organisation'];
                $organisation   = $this->organisationManager->findOrganisationById($organisationId);

                try {
                    $uri = $this->router->generateUri($route, ['org_id' => $organisation->getUuid()]);
                } catch (Exception $exception) {
                    return new HtmlResponse($this->renderer->render('error::404'), 404);
                }
                return new RedirectResponse($uri);
            }
        }

        return new HtmlResponse($this->renderer->render('organisation::select', [
            'form' => $form,
        ]));
    }
}
