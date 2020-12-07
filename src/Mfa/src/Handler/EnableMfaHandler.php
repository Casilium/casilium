<?php
declare(strict_types=1);

namespace Mfa\Handler;

use App\Traits\CsrfTrait;
use Exception;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Csrf\CsrfMiddleware;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Flash\FlashMessagesInterface;
use Mezzio\Helper\UrlHelper;
use Mezzio\Session\Session;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Template\TemplateRendererInterface;
use Mfa\Form\GoogleMfaForm;
use Mfa\Service\MfaService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use User\Service\UserManager;
use UserAuthentication\Entity\IdentityInterface;
use function gettype;
use function is_array;
use function sprintf;

/**
 * Enable MFA Handler
 *
 * Used to allow the user to enable MFA, displays form/qr-code and verifies validation code
 */
class EnableMfaHandler implements RequestHandlerInterface
{
    use CsrfTrait;

    /** @var MfaService */
    private $mfaService;

    /** @var UserManager */
    private $userManager;

    /** @var UrlHelper */
    private $helper;

    /** @var TemplateRendererInterface */
    private $renderer;

    public function __construct(
        MfaService $mfaService,
        UserManager $userManager,
        TemplateRendererInterface $renderer,
        UrlHelper $helper
    ) {
        $this->mfaService  = $mfaService;
        $this->userManager = $userManager;
        $this->renderer    = $renderer;
        $this->helper      = $helper;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // get user from session
        $user = $request->getAttribute(IdentityInterface::class);
        $key  = $this->mfaService->getSecretKey($user);

        if ($this->mfaService->hasMfa($user)) {
            return new RedirectResponse($this->helper->generate('account'));
        }

        /** @var Session $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        // get csrf token
        $guard = $request->getAttribute(CsrfMiddleware::GUARD_ATTRIBUTE);
        $token = $this->getToken($session, $guard);

        $error = null;
        $form  = new GoogleMfaForm($guard);

        if ($request->getMethod() === 'POST') {
            // populate form from POST vars
            $form->setData($request->getParsedBody());
            if ($form->isValid()) {
                // grab form data
                $data = $form->getData();
                if (! is_array($data)) {
                    throw new Exception(sprintf(
                        'Expected return type from form was array, got %s',
                        gettype($data)
                    ));
                }
                $key = $data['secret_key'];
                $pin = $data['pin'];

                if ($this->mfaService->isValidCode($key, $pin)) {
                    $this->mfaService->enableMfa($user);

                    /** @var FlashMessagesInterface $flashMessages */
                    $flashMessages = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);
                    $flashMessages->flash('mfa', 'MFA is now enabled for you account.');

                    return new RedirectResponse($this->helper->generate('home'));
                }
            }

            $error = 'Invalid key';
            // regenerate token on failure
            $token = $this->getToken($session, $guard);
        }

        return new HtmlResponse($this->renderer->render('mfa::mfa-page', [
            'form'       => $form,
            'qrcode_url' => $this->mfaService->getQrCodeUrl($user->getEmail(), $key),
            'secret_key' => $key,
            'token'      => $token,
            'error'      => $error,
        ]));
    }
}
