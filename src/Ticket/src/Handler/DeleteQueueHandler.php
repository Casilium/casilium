<?php

declare(strict_types=1);

namespace Ticket\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ticket\Service\QueueManager;

class DeleteQueueHandler implements RequestHandlerInterface
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
        $queueId = (int) $request->getAttribute('id');
        $queue   = $this->queueManager->findQueueById($queueId);

        $confirm = $request->getAttribute('confirm');
        if (null !== $confirm) {
            if ($confirm === 'true') {
                $this->queueManager->delete($queueId);
            }
            return new RedirectResponse($this->urlHelper->generate('admin.queue_list'));
        }

        return new HtmlResponse($this->renderer->render('ticket::delete-queue', ['queue' => $queue]));
    }
}
