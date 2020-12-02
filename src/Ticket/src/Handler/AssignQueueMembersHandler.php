<?php

declare(strict_types=1);

namespace Ticket\Handler;

use Exception;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Form\FormInterface;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ticket\Entity\Agent;
use Ticket\Entity\QueueMember;
use Ticket\Form\AssignQueueMembersForm;
use Ticket\Service\QueueManager;

class AssignQueueMembersHandler implements RequestHandlerInterface
{
    /** @var QueueManager */
    protected $queueManager;

    /** @var TemplateRendererInterface */
    protected $renderer;

    /** @var UrlHelper */
    protected $urlHelper;

    public function __construct(
        QueueManager $queueManager,
        TemplateRendererInterface $renderer,
        UrlHelper $urlHelper
    ) {
        $this->queueManager = $queueManager;
        $this->renderer     = $renderer;
        $this->urlHelper    = $urlHelper;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $queueId = (int) $request->getAttribute('id', 0);
        if ($queueId === 0) {
            throw new Exception('Queue not found');
        }

        $queue = $this->queueManager->findQueueById($queueId);
        if ($queue === null) {
            throw new Exception('Queue not found');
        }

        $form = new AssignQueueMembersForm();
        $form->get('members')->setValueOptions($this->queueManager->findMemberOptions());
        $form->setValue($this->getCurrentQueueMembers($queueId));

        if ($request->getMethod() === 'POST') {
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                $this->queueManager->assignQueueMembers($queueId, $form->getData());
            }
        }

        return new HtmlResponse($this->renderer->render('ticket::assign-queue-members', [
            'queue' => $queue,
            'form'  => $form,
        ]));
    }

    public function getCurrentQueueMembers(int $queueId): array
    {
        /** @var Agent[] $members */
        $members = $this->queueManager->findQueueMembers($queueId);

        if (empty($members)) {
            return [];
        }

        $result = [];
        foreach ($members as $member) {
            $result[$member->getId()] = $member->getFullName();
        }

        return $result;
    }
}
