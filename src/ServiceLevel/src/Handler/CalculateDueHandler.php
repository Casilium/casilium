<?php

declare(strict_types=1);

namespace ServiceLevel\Handler;

use Carbon\Carbon;
use Laminas\Diactoros\Response\TextResponse;
use Organisation\Service\OrganisationManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ServiceLevel\Entity\SlaTarget;
use ServiceLevel\Service\CalculateBusinessHours;

use function strcmp;

class CalculateDueHandler implements RequestHandlerInterface
{
    /** @var OrganisationManager */
    protected $organisationManager;

    public function __construct(OrganisationManager $organisationManager)
    {
        $this->organisationManager = $organisationManager;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // get organisation from url
        $orgId        = $request->getAttribute('org_id');
        $organisation = $this->organisationManager->findOrganisationByUuid($orgId);

        // get priority and response type from url
        $priority = (int) $request->getAttribute('priority');
        $type     = $request->getAttribute('type');

        /** @var SlaTarget[] $orgTargets */
        $orgTargets = $organisation->getSla()->getSlaTargets();
        $targets    = [];
        foreach ($orgTargets as $target) {
            $targets[$target->getPriority()->getId()] = $target;
        }

        // get resolve time from SLA
        $duration = null;
        if (strcmp('resolve', $type) === 0) {
            $duration = $targets[$priority]->getResolveTime();
        } else {
            $duration = $targets[$priority]->getResponseTime();
        }

        $calc   = new CalculateBusinessHours($organisation->getSla()->getBusinessHours());
        $result = $calc->addHoursTo(Carbon::now(), $duration);

        return new TextResponse($result->format('Y-m-d H:i:s'));
    }
}
