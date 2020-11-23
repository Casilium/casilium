<?php

declare(strict_types=1);

namespace Ticket\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ticket\Entity\Queue;
use Ticket\Form\QueueForm;
use Ticket\Service\QueueManager;

class CreateQueueHandler implements RequestHandlerInterface
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
        $form = new QueueForm();
        $form->bind(new Queue());

        if ($request->getMethod() === 'POST') {
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                $this->queueManager->save($form->getData());
            }
        }

        return new HtmlResponse($this->renderer->render('ticket::create-queue', [
            'form' => $form,
        ]));
    }
}
