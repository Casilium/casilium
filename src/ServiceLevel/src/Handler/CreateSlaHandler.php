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
use ServiceLevel\Entity\Sla;
use ServiceLevel\Form\SlaForm;
use ServiceLevel\Hydrator\SlaHydrator;
use ServiceLevel\Service\SlaService;

class CreateSlaHandler implements RequestHandlerInterface
{
    /** @var SlaService */
    protected $slaService;

    /** @var SlaHydrator */
    protected $hydrator;

    /** @var TemplateRendererInterface */
    protected $renderer;

    /** @var UrlHelper */
    protected $urlHelper;

    public function __construct(
        SlaService $slaService,
        SlaHydrator $hydrator,
        TemplateRendererInterface $renderer,
        UrlHelper $urlHelper
    ) {
        $this->slaService = $slaService;
        $this->hydrator   = $hydrator;
        $this->renderer   = $renderer;
        $this->urlHelper  = $urlHelper;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $form = new SlaForm();
        $form->setHydrator($this->hydrator);
        $form->bind(new Sla());

        $form->get('business_hours')->setValueOptions($this->populateBusinessHours());

        if ($request->getMethod() === 'POST') {
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                $data = $form->getData(FormInterface::VALUES_AS_ARRAY);
                $this->slaService->createSla($data);
                return new RedirectResponse($this->urlHelper->generate('sla.list'));
            }
        }

        return new HtmlResponse($this->renderer->render('sla::create-sla', [
            'form'   => $form,
            'action' => 'New',
        ]));
    }

    public function populateBusinessHours(): array
    {
        $result = $this->slaService->findAllBusinessHours();

        $businessHours = [];
        foreach ($result as $businessHour) {
            $businessHours[$businessHour->getId()] = $businessHour->getName();
        }
        return $businessHours;
    }
}
