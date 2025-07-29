<?php

declare(strict_types=1);

namespace AccountTest\Handler;

use Account\Handler\ChangePasswordHandler;
use Exception;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\ServerRequest;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Flash\FlashMessagesInterface;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;
use User\Form\PasswordChangeForm;
use User\Service\UserManager;
use UserAuthentication\Entity\IdentityInterface;

class ChangePasswordHandlerTest extends TestCase
{
    private ChangePasswordHandler $handler;
    private UserManager $userManager;
    private TemplateRendererInterface $renderer;
    private UrlHelper $urlHelper;

    protected function setUp(): void
    {
        $this->userManager = $this->createMock(UserManager::class);
        $this->renderer    = $this->createMock(TemplateRendererInterface::class);
        $this->urlHelper   = $this->createMock(UrlHelper::class);

        $this->handler = new ChangePasswordHandler(
            $this->userManager,
            $this->renderer,
            $this->urlHelper
        );
    }

    public function testConstructorSetsProperties(): void
    {
        $handler = new ChangePasswordHandler(
            $this->userManager,
            $this->renderer,
            $this->urlHelper
        );

        $this->assertInstanceOf(ChangePasswordHandler::class, $handler);
    }

    public function testHandleGetRequestRendersForm(): void
    {
        $user          = $this->createMockUser(123);
        $flashMessages = $this->createMock(FlashMessagesInterface::class);

        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withAttribute(IdentityInterface::class, $user)
                          ->withAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE, $flashMessages);

        $this->renderer->expects($this->once())
            ->method('render')
            ->with(
                'account::change-password',
                $this->callback(function ($data) {
                    return isset($data['form']) && $data['form'] instanceof PasswordChangeForm;
                })
            )
            ->willReturn('<html>Change password form</html>');

        $response = $this->handler->handle($request);

        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertEquals('<html>Change password form</html>', $response->getBody()->getContents());
    }

    public function testHandlePostRequestWithValidDataChangesPassword(): void
    {
        $user          = $this->createMockUser(123);
        $flashMessages = $this->createMock(FlashMessagesInterface::class);

        // Use correct field names based on PasswordChangeForm
        $postData = [
            'current_password'     => 'oldpass123',
            'new_password'         => 'newpass456',
            'confirm_new_password' => 'newpass456', // Note: correct field name
        ];

        $request = new ServerRequest();
        $request = $request->withMethod('POST')
                          ->withParsedBody($postData)
                          ->withAttribute(IdentityInterface::class, $user)
                          ->withAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE, $flashMessages);

        $this->userManager->expects($this->once())
            ->method('changePassword')
            ->with(123, 'oldpass123', 'newpass456');

        $flashMessages->expects($this->once())
            ->method('flash')
            ->with('info', 'Password Changed');

        $this->urlHelper->expects($this->once())
            ->method('generate')
            ->with('account')
            ->willReturn('/account');

        $response = $this->handler->handle($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/account', $response->getHeaderLine('Location'));
    }

    public function testHandlePostRequestWithInvalidDataShowsErrors(): void
    {
        $user          = $this->createMockUser(123);
        $flashMessages = $this->createMock(FlashMessagesInterface::class);

        $postData = [
            'current_password'     => 'wrong',
            'new_password'         => 'new',
            'confirm_new_password' => 'different',
        ];

        $request = new ServerRequest();
        $request = $request->withMethod('POST')
                          ->withParsedBody($postData)
                          ->withAttribute(IdentityInterface::class, $user)
                          ->withAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE, $flashMessages);

        $this->renderer->expects($this->once())
            ->method('render')
            ->with(
                'account::change-password',
                $this->callback(function ($data) {
                    return isset($data['form']) && $data['form'] instanceof PasswordChangeForm;
                })
            )
            ->willReturn('<html>Form with validation errors</html>');

        $response = $this->handler->handle($request);

        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertEquals('<html>Form with validation errors</html>', $response->getBody()->getContents());
    }

    public function testHandlePostRequestWithUserManagerExceptionShowsError(): void
    {
        $user          = $this->createMockUser(123);
        $flashMessages = $this->createMock(FlashMessagesInterface::class);

        $postData = [
            'current_password'     => 'oldpass123',
            'new_password'         => 'newpass456',
            'confirm_new_password' => 'newpass456',
        ];

        $request = new ServerRequest();
        $request = $request->withMethod('POST')
                          ->withParsedBody($postData)
                          ->withAttribute(IdentityInterface::class, $user)
                          ->withAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE, $flashMessages);

        $this->userManager->expects($this->once())
            ->method('changePassword')
            ->with(123, 'oldpass123', 'newpass456')
            ->willThrowException(new Exception('Current password is incorrect'));

        $this->renderer->expects($this->once())
            ->method('render')
            ->with('account::change-password', $this->isType('array'))
            ->willReturn('<html>Form with error message</html>');

        $response = $this->handler->handle($request);

        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertEquals('<html>Form with error message</html>', $response->getBody()->getContents());
    }

    public function testHandleRetrievesUserIdFromIdentity(): void
    {
        $user          = $this->createMockUser(456);
        $flashMessages = $this->createMock(FlashMessagesInterface::class);

        $postData = [
            'current_password'     => 'oldpass123',
            'new_password'         => 'newpass456',
            'confirm_new_password' => 'newpass456',
        ];

        $request = new ServerRequest();
        $request = $request->withMethod('POST')
                          ->withParsedBody($postData)
                          ->withAttribute(IdentityInterface::class, $user)
                          ->withAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE, $flashMessages);

        $this->userManager->expects($this->once())
            ->method('changePassword')
            ->with($this->equalTo(456), $this->anything(), $this->anything());

        $this->urlHelper->method('generate')->willReturn('/account');

        $this->handler->handle($request);
    }

    public function testHandleWithNullUserIdThrowsException(): void
    {
        // Test that the bug has been fixed: user with null ID should throw exception

        $user          = $this->createMockUser(null); // User with null ID
        $flashMessages = $this->createMock(FlashMessagesInterface::class);

        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withAttribute(IdentityInterface::class, $user)
                          ->withAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE, $flashMessages);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User not logged!?');

        $this->handler->handle($request);
    }

    public function testHandleUsesCorrectTemplateForPasswordChange(): void
    {
        $user          = $this->createMockUser(123);
        $flashMessages = $this->createMock(FlashMessagesInterface::class);

        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withAttribute(IdentityInterface::class, $user)
                          ->withAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE, $flashMessages);

        $this->renderer->expects($this->once())
            ->method('render')
            ->with($this->equalTo('account::change-password'), $this->anything())
            ->willReturn('');

        $this->handler->handle($request);
    }

    public function testHandleCreatesPasswordChangeFormWithCorrectName(): void
    {
        $user          = $this->createMockUser(123);
        $flashMessages = $this->createMock(FlashMessagesInterface::class);

        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withAttribute(IdentityInterface::class, $user)
                          ->withAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE, $flashMessages);

        $this->renderer->expects($this->once())
            ->method('render')
            ->with(
                $this->anything(),
                $this->callback(function ($data) {
                    return isset($data['form']) &&
                           $data['form'] instanceof PasswordChangeForm &&
                           $data['form']->getName() === 'password-change-form';
                })
            )
            ->willReturn('');

        $this->handler->handle($request);
    }

    public function testHandleRedirectsToAccountPageAfterSuccessfulChange(): void
    {
        $user          = $this->createMockUser(123);
        $flashMessages = $this->createMock(FlashMessagesInterface::class);

        $postData = [
            'current_password'     => 'oldpass123',
            'new_password'         => 'newpass456',
            'confirm_new_password' => 'newpass456',
        ];

        $request = new ServerRequest();
        $request = $request->withMethod('POST')
                          ->withParsedBody($postData)
                          ->withAttribute(IdentityInterface::class, $user)
                          ->withAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE, $flashMessages);

        $this->userManager->method('changePassword');
        $flashMessages->method('flash');

        $this->urlHelper->expects($this->once())
            ->method('generate')
            ->with('account')
            ->willReturn('/account-page');

        $response = $this->handler->handle($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/account-page', $response->getHeaderLine('Location'));
    }

    public function testHandleWithFormValidationFailure(): void
    {
        $user          = $this->createMockUser(123);
        $flashMessages = $this->createMock(FlashMessagesInterface::class);

        // Invalid data - missing required fields
        $postData = [
            'current_password' => '',
            'new_password'     => '',
            'confirm_password' => '',
        ];

        $request = new ServerRequest();
        $request = $request->withMethod('POST')
                          ->withParsedBody($postData)
                          ->withAttribute(IdentityInterface::class, $user)
                          ->withAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE, $flashMessages);

        // UserManager should not be called if form validation fails
        $this->userManager->expects($this->never())
            ->method('changePassword');

        $this->renderer->expects($this->once())
            ->method('render')
            ->willReturn('<html>Form with validation errors</html>');

        $response = $this->handler->handle($request);

        $this->assertInstanceOf(HtmlResponse::class, $response);
    }

    private function createMockUser(?int $id): IdentityInterface
    {
        $user = $this->createMock(IdentityInterface::class);
        $user->method('getId')->willReturn($id);
        return $user;
    }
}
