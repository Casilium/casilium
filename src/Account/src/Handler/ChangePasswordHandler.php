<?php

declare(strict_types=1);

namespace Account\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Authentication\UserInterface;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Helper\UrlHelper;
use Mezzio\Session\Session;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use User\Form\PasswordChangeForm;
use User\Service\UserManager;

/**
 * Change user account password
 *
 * Class ChangePasswordHandler
 * @package Account\Handler
 */
class ChangePasswordHandler implements RequestHandlerInterface
{
    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * @var TemplateRendererInterface
     */
    protected $renderer;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    /**
     * ChangePasswordHandler constructor.
     *
     * @param UserManager $userManager
     * @param TemplateRendererInterface $renderer
     * @param UrlHelper $urlHelper
     */
    public function __construct(
        UserManager $userManager,
        TemplateRendererInterface  $renderer,
        UrlHelper $urlHelper
    )
    {
        $this->userManager = $userManager;
        $this->renderer = $renderer;
        $this->urlHelper = $urlHelper;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var Session $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        $flashMessages = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);

        // get user from session
        $user = $session->get(UserInterface::class);
        $user_id = (int)$user['details']['id'];
        if (null === 0) {
            throw new \Exception('User not logged!?');
        }

        $form = new PasswordChangeForm('change');

        if ($request->getMethod() === 'POST') {

            // set form data from POST vars
            $form->setData($request->getParsedBody());
            if ($form->isValid()) {
                $data = $form->getData();

                try {
                    $this->userManager->changePassword($user_id, $data['current_password'], $data['new_password']);
                } catch (\Exception $exception) {
                    $form->get('current_password')->setMessages([$exception->getMessage()]);
                    return new HtmlResponse($this->renderer->render('account::change-password', ['form' => $form]));
                }

                $flashMessages->flash('info', 'Password Changed');
                return new RedirectResponse($this->urlHelper->generate('account'));
            }
        }

        return new HtmlResponse($this->renderer->render('account::change-password', ['form' => $form]));
    }
}