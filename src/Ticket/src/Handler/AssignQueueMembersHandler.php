<?php

declare(strict_types=1);

namespace Ticket\Handler;

use Exception;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ticket\Entity\Agent;
use Ticket\Entity\Queue;
use Ticket\Form\AssignQueueMembersForm;
use Ticket\Service\QueueManager;
use function array_keys;

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

        $activeMembers = array_keys($this->getCurrentQueueMembers($queue));
        $form->get('members')->setValue($activeMembers);

        if ($request->getMethod() === 'POST') {
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                $this->queueManager->assignQueueMembers($queueId, $form->getData()['members']);
            }

            return new RedirectResponse($this->urlHelper->generate('admin.queue_list'));
        }

        return new HtmlResponse($this->renderer->render('ticket::assign-queue-members', [
            'queue' => $queue,
            'form'  => $form,
        ]));
    }

    public function getCurrentQueueMembers(Queue $queue): array
    {
        $result = [];
        foreach ($queue->getMembers() as $member) {
            $result[$member->getId()] = $member->getFullName();
        }
        return $result;
    }
}
