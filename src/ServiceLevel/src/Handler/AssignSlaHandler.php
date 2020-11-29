<?php

declare(strict_types=1);

namespace ServiceLevel\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Form\FormInterface;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Organisation\Service\OrganisationManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ServiceLevel\Entity\Sla;
use ServiceLevel\Form\AssignSlaForm;
use ServiceLevel\Service\SlaService;

class AssignSlaHandler implements RequestHandlerInterface
{
    /** @var SlaService */
    protected $slaService;

    /** @var OrganisationManager */
    protected $organisationManager;

    /** @var TemplateRendererInterface */
    protected $renderer;

    /** @var UrlHelper */
    protected $urlHelper;

    public function __construct(
        SlaService $slaService,
        OrganisationManager $organisationManager,
        TemplateRendererInterface $renderer,
        UrlHelper $urlHelper
    ) {
        $this->slaService          = $slaService;
        $this->organisationManager = $organisationManager;
        $this->renderer            = $renderer;
        $this->urlHelper           = $urlHelper;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $orgId        = $request->getAttribute('org_id');
        $organisation = $this->organisationManager->findOrganisationByUuid($orgId);

        $form = new AssignSlaForm();
        $this->populateSlaPolicies($form);

        if ($request->getMethod() === 'POST') {
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                $data = $form->getData();

                $slaId = (int) $data['sla_id'];
                $this->slaService->assignOrganisationSla($organisation->getId(), $slaId);

                return new RedirectResponse($this->urlHelper->generate('organisation.view', [
                    'id' => $organisation->getUuid(),
                ]));
            }
        }

        return new HtmlResponse($this->renderer->render('sla::assign-sla', [
            'form'         => $form,
            'organisation' => $organisation,
        ]));
    }

    public function populateSlaPolicies(FormInterface $form): void
    {
        $result = [];
        /** @var Sla[] $policies */
        $policies = $this->slaService->findAllSlaPolicies();
        foreach ($policies as $policy) {
            $result[$policy->getId()] = $policy->getName();
        }

        $form->get('sla_id')->setValueOptions($result);
    }
}
