<?php

declare(strict_types=1);

namespace ServiceLevel\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Form\FormInterface;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ServiceLevel\Form\BusinessHoursForm;
use ServiceLevel\Service\SlaService;

class EditBusinessHoursHandler implements RequestHandlerInterface
{
    /** @var SlaService */
    protected $slaService;

    /** @var TemplateRendererInterface */
    protected $renderer;

    /** @var UrlHelper */
    protected $urlHelper;

    public function __construct(SlaService $slaService, TemplateRendererInterface $renderer, UrlHelper $urlHelper)
    {
        $this->slaService = $slaService;
        $this->renderer   = $renderer;
        $this->urlHelper  = $urlHelper;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id            = (int) $request->getAttribute('id');
        $businessHours = $this->slaService->findBusinessHoursById($id);

        $form = new BusinessHoursForm();
        $form->bind($businessHours);

        if ($request->getMethod() === 'POST') {
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                $businessHours = $this->slaService->saveBusinessHours(
                    $form->getData(FormInterface::VALUES_AS_ARRAY)
                );

                return new RedirectResponse($this->urlHelper->generate('sla.list_business_hours'));
            }
        }

        return new HtmlResponse($this->renderer->render('sla::create-business-hours', [
            'form'   => $form,
            'action' => 'Edit',
        ]));
    }
}
