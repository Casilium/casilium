<?php

declare(strict_types=1);

namespace TicketTest\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\ServerRequest;
use Mezzio\Csrf\CsrfMiddleware;
use Mezzio\Csrf\SessionCsrfGuard;
use Mezzio\Helper\UrlHelper;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;
use Ticket\Entity\CannedResponse;
use Ticket\Form\CannedResponseForm;
use Ticket\Handler\EditCannedResponseHandler;
use Ticket\Service\CannedResponseManager;

class EditCannedResponseHandlerTest extends TestCase
{
    private CannedResponseManager $manager;
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;
    private TemplateRendererInterface $renderer;
    private UrlHelper $urlHelper;
    private SessionCsrfGuard $guard;
    private SessionInterface $session;
    private EditCannedResponseHandler $handler;

    protected function setUp(): void
    {
        $this->manager       = $this->createMock(CannedResponseManager::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository    = $this->createMock(EntityRepository::class);
        $this->renderer      = $this->createMock(TemplateRendererInterface::class);
        $this->urlHelper     = $this->createMock(UrlHelper::class);
        $this->guard         = $this->createMock(SessionCsrfGuard::class);
        $this->session       = $this->createMock(SessionInterface::class);

        $this->session->method('has')->with('__csrf')->willReturn(false);
        $this->guard->method('generateToken')->willReturn('csrf-token');
        $this->entityManager->method('getRepository')
            ->with(CannedResponse::class)
            ->willReturn($this->repository);

        $this->handler = new EditCannedResponseHandler(
            $this->manager,
            $this->entityManager,
            $this->renderer,
            $this->urlHelper
        );
    }

    public function testRendersCreateForm(): void
    {
        $this->renderer->expects($this->once())
            ->method('render')
            ->with(
                'ticket::edit-canned-response',
                $this->callback(static function (array $data): bool {
                    return $data['form'] instanceof CannedResponseForm
                        && $data['token'] === 'csrf-token'
                        && $data['cannedResponse'] === null;
                })
            )
            ->willReturn('<html>Create</html>');

        $response = $this->handler->handle($this->request('GET'));

        $this->assertInstanceOf(HtmlResponse::class, $response);
    }

    public function testRendersExistingResponseForEditing(): void
    {
        $cannedResponse = new CannedResponse();
        $cannedResponse->setId(5)->setTitle('Password reset')->setResponse('Reset it.');

        $this->manager->expects($this->once())->method('findById')->with(5)->willReturn($cannedResponse);
        $this->renderer->expects($this->once())
            ->method('render')
            ->with(
                'ticket::edit-canned-response',
                $this->callback(static function (array $data) use ($cannedResponse): bool {
                    return $data['cannedResponse'] === $cannedResponse
                        && $data['form']->get('title')->getValue() === 'Password reset'
                        && $data['form']->get('response')->getValue() === 'Reset it.';
                })
            )
            ->willReturn('<html>Edit</html>');

        $response = $this->handler->handle($this->request('GET')->withAttribute('id', '5'));

        $this->assertInstanceOf(HtmlResponse::class, $response);
    }

    public function testReturnsNotFoundForUnknownResponse(): void
    {
        $this->manager->expects($this->once())->method('findById')->with(99)->willReturn(null);
        $this->renderer->expects($this->once())
            ->method('render')
            ->with('error::404')
            ->willReturn('<html>Not found</html>');

        $response = $this->handler->handle($this->request('GET')->withAttribute('id', '99'));

        $this->assertSame(404, $response->getStatusCode());
    }

    public function testValidPostSavesAndRedirects(): void
    {
        $this->repository->method('findOneBy')->willReturn(null);
        $this->guard->method('validateToken')->willReturn(true);

        $this->manager->expects($this->once())
            ->method('save')
            ->with($this->callback(static function (array $data): bool {
                return $data['title'] === 'Password reset'
                    && $data['response'] === 'Please reset your password.';
            }))
            ->willReturn(new CannedResponse());
        $this->urlHelper->expects($this->once())
            ->method('generate')
            ->with('admin.canned_response.list')
            ->willReturn('/admin/ticket/canned-response');

        $request  = $this->request('POST')->withParsedBody([
            'title'    => 'Password reset',
            'response' => 'Please reset your password.',
            'csrf'     => 'csrf-token',
        ]);
        $response = $this->handler->handle($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/admin/ticket/canned-response', $response->getHeaderLine('Location'));
    }

    private function request(string $method): ServerRequest
    {
        return (new ServerRequest())
            ->withMethod($method)
            ->withAttribute(SessionMiddleware::SESSION_ATTRIBUTE, $this->session)
            ->withAttribute(CsrfMiddleware::GUARD_ATTRIBUTE, $this->guard);
    }
}
