<?php

declare(strict_types=1);

namespace TicketTest\Handler;

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
use Ticket\Handler\DeleteCannedResponseHandler;
use Ticket\Service\CannedResponseManager;

class DeleteCannedResponseHandlerTest extends TestCase
{
    public function testGetRendersConfirmationWithoutDeleting(): void
    {
        $manager        = $this->createMock(CannedResponseManager::class);
        $renderer       = $this->createMock(TemplateRendererInterface::class);
        $urlHelper      = $this->createMock(UrlHelper::class);
        $guard          = $this->createMock(SessionCsrfGuard::class);
        $session        = $this->createMock(SessionInterface::class);
        $cannedResponse = $this->cannedResponse();

        $session->method('has')->with('__csrf')->willReturn(false);
        $guard->method('generateToken')->willReturn('csrf-token');
        $manager->method('findById')->with(7)->willReturn($cannedResponse);
        $manager->expects($this->never())->method('delete');
        $renderer->expects($this->once())
            ->method('render')
            ->with(
                'ticket::delete-canned-response',
                $this->callback(static function (array $data) use ($cannedResponse): bool {
                    return $data['cannedResponse'] === $cannedResponse
                        && $data['token'] === 'csrf-token';
                })
            )
            ->willReturn('<html>Confirm</html>');

        $handler  = new DeleteCannedResponseHandler($manager, $renderer, $urlHelper);
        $response = $handler->handle($this->request('GET', $session, $guard));

        $this->assertInstanceOf(HtmlResponse::class, $response);
    }

    public function testValidPostDeletesAndRedirects(): void
    {
        $manager        = $this->createMock(CannedResponseManager::class);
        $renderer       = $this->createMock(TemplateRendererInterface::class);
        $urlHelper      = $this->createMock(UrlHelper::class);
        $guard          = $this->createMock(SessionCsrfGuard::class);
        $session        = $this->createMock(SessionInterface::class);
        $cannedResponse = $this->cannedResponse();

        $session->method('has')->with('__csrf')->willReturn(false);
        $guard->method('generateToken')->willReturn('csrf-token');
        $guard->method('validateToken')->with('csrf-token')->willReturn(true);
        $manager->method('findById')->with(7)->willReturn($cannedResponse);
        $manager->expects($this->once())->method('delete')->with($cannedResponse);
        $urlHelper->method('generate')
            ->with('admin.canned_response.list')
            ->willReturn('/admin/ticket/canned-response');

        $handler  = new DeleteCannedResponseHandler($manager, $renderer, $urlHelper);
        $request  = $this->request('POST', $session, $guard)
            ->withParsedBody(['csrf' => 'csrf-token', 'submit' => 'delete']);
        $response = $handler->handle($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/admin/ticket/canned-response', $response->getHeaderLine('Location'));
    }

    private function cannedResponse(): CannedResponse
    {
        $response = new CannedResponse();
        return $response->setId(7)->setTitle('Password reset')->setResponse('Reset it.');
    }

    private function request(
        string $method,
        SessionInterface $session,
        SessionCsrfGuard $guard
    ): ServerRequest {
        return (new ServerRequest())
            ->withMethod($method)
            ->withAttribute('id', '7')
            ->withAttribute(SessionMiddleware::SESSION_ATTRIBUTE, $session)
            ->withAttribute(CsrfMiddleware::GUARD_ATTRIBUTE, $guard);
    }
}
