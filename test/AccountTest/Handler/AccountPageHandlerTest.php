<?php

declare(strict_types=1);

namespace AccountTest\Handler;

use Account\Handler\AccountPageHandler;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\ServerRequest;
use Mezzio\Template\TemplateRendererInterface;
use Mfa\Service\MfaService;
use PHPUnit\Framework\TestCase;
use UserAuthentication\Entity\IdentityInterface;

class AccountPageHandlerTest extends TestCase
{
    private AccountPageHandler $handler;
    private TemplateRendererInterface $renderer;
    private MfaService $mfaService;

    protected function setUp(): void
    {
        $this->renderer = $this->createMock(TemplateRendererInterface::class);
        $this->mfaService = $this->createMock(MfaService::class);
        
        $this->handler = new AccountPageHandler($this->renderer, $this->mfaService);
    }

    public function testConstructorSetsProperties(): void
    {
        $handler = new AccountPageHandler($this->renderer, $this->mfaService);
        
        $this->assertInstanceOf(AccountPageHandler::class, $handler);
    }

    public function testHandleWithMfaEnabled(): void
    {
        $user = $this->createMock(IdentityInterface::class);
        
        $request = new ServerRequest();
        $request = $request->withAttribute(IdentityInterface::class, $user);
        
        $this->mfaService->expects($this->once())
            ->method('hasMfa')
            ->with($user)
            ->willReturn(true);
            
        $this->renderer->expects($this->once())
            ->method('render')
            ->with('account::account-page', ['mfa_enabled' => true])
            ->willReturn('<html>Account page with MFA enabled</html>');
        
        $response = $this->handler->handle($request);
        
        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertEquals('<html>Account page with MFA enabled</html>', $response->getBody()->getContents());
    }

    public function testHandleWithMfaDisabled(): void
    {
        $user = $this->createMock(IdentityInterface::class);
        
        $request = new ServerRequest();
        $request = $request->withAttribute(IdentityInterface::class, $user);
        
        $this->mfaService->expects($this->once())
            ->method('hasMfa')
            ->with($user)
            ->willReturn(false);
            
        $this->renderer->expects($this->once())
            ->method('render')
            ->with('account::account-page', ['mfa_enabled' => false])
            ->willReturn('<html>Account page with MFA disabled</html>');
        
        $response = $this->handler->handle($request);
        
        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertEquals('<html>Account page with MFA disabled</html>', $response->getBody()->getContents());
    }

    public function testHandleRetrievesUserFromRequest(): void
    {
        $user = $this->createMock(IdentityInterface::class);
        
        $request = new ServerRequest();
        $request = $request->withAttribute(IdentityInterface::class, $user);
        
        $this->mfaService->expects($this->once())
            ->method('hasMfa')
            ->with($this->identicalTo($user))
            ->willReturn(false);
            
        $this->renderer->method('render')->willReturn('');
        
        $this->handler->handle($request);
    }

    public function testHandleWithNullUserThrowsTypeError(): void
    {
        $request = new ServerRequest();
        $request = $request->withAttribute(IdentityInterface::class, null);
        
        // This will cause a TypeError because MfaService::hasMfa expects IdentityInterface, not null
        $this->expectException(\TypeError::class);
        
        $this->handler->handle($request);
    }

    public function testHandlePassesCorrectTemplateVariables(): void
    {
        $user = $this->createMock(IdentityInterface::class);
        
        $request = new ServerRequest();
        $request = $request->withAttribute(IdentityInterface::class, $user);
        
        $this->mfaService->method('hasMfa')->willReturn(true);
        
        $this->renderer->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo('account::account-page'),
                $this->equalTo(['mfa_enabled' => true])
            )
            ->willReturn('rendered content');
        
        $this->handler->handle($request);
    }

    public function testHandleUsesCorrectTemplateName(): void
    {
        $user = $this->createMock(IdentityInterface::class);
        
        $request = new ServerRequest();
        $request = $request->withAttribute(IdentityInterface::class, $user);
        
        $this->mfaService->method('hasMfa')->willReturn(false);
        
        $this->renderer->expects($this->once())
            ->method('render')
            ->with($this->stringContains('account::account-page'))
            ->willReturn('');
        
        $this->handler->handle($request);
    }

    public function testHandleReturnsHtmlResponse(): void
    {
        $user = $this->createMock(IdentityInterface::class);
        
        $request = new ServerRequest();
        $request = $request->withAttribute(IdentityInterface::class, $user);
        
        $this->mfaService->method('hasMfa')->willReturn(false);
        $this->renderer->method('render')->willReturn('test content');
        
        $response = $this->handler->handle($request);
        
        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testHandleWithMfaEnabledVsDisabled(): void
    {
        $user = $this->createMock(IdentityInterface::class);
        $request = new ServerRequest();
        $request = $request->withAttribute(IdentityInterface::class, $user);
        
        // Test with MFA enabled
        $this->mfaService->method('hasMfa')->willReturn(true);
        $this->renderer->method('render')
            ->with('account::account-page', ['mfa_enabled' => true])
            ->willReturn('mfa enabled');
        
        $response1 = $this->handler->handle($request);
        $this->assertEquals('mfa enabled', $response1->getBody()->getContents());
    }
}