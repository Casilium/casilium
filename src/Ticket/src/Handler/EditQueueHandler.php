<?php

declare(strict_types=1);

namespace Ticket\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Form\FormInterface;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ticket\Form\QueueForm;
use Ticket\Service\QueueManager;

class EditQueueHandler implements RequestHandlerInterface
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

        $form = new QueueForm();
        $form->bind($queue);
        $form->get('use_ssl')->setValue($queue->getUseSsl() ? '1' : '0');
        $form->get('fetch_from_mail')->setValue($queue->getFetchFromMail() ? '1' : '0');

        if ($request->getMethod() === 'POST') {
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                $this->queueManager->save($form->getData(FormInterface::VALUES_AS_ARRAY));
                return new RedirectResponse($this->urlHelper->generate('admin.queue_list'));
            }
        }

        return new HtmlResponse($this->renderer->render('ticket::create-queue', [
            'form' => $form,
        ]));
    }
}
