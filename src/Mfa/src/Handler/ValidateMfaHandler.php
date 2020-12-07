<?php
declare(strict_types=1);

namespace Mfa\Handler;

use App\Traits\CsrfTrait;
use Exception;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Csrf\CsrfMiddleware;
use Mezzio\Helper\UrlHelper;
use Mezzio\Session\Session;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Template\TemplateRendererInterface;
use Mfa\Form\GoogleMfaForm;
use Mfa\Service\MfaService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use User\Service\UserManager;
use UserAuthentication\Entity\IdentityInterface;
use function gettype;
use function is_array;
use function sprintf;

/**
 * Handles MFA on user login
 */
class ValidateMfaHandler implements MiddlewareInterface
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

    /**
     * @throws Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Session $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        // if mfa is enabled the user id will exist in session, if not continue on
        if (! $session->has('mfa:user:id')) {
            return $handler->handle($request);
        }

        // get user id from session
        $userId = (int) $session->get('mfa:user:id');
        if ($userId === 0) {
            throw new \Exception('MFA User ID not found');
        }

        // find user attempting to verify MFA
        $user = $this->userManager->findById($userId);
        if ($user === null) {
            throw new \Exception('User not found');
        }

        // generate csrf
        $guard = $request->getAttribute(CsrfMiddleware::GUARD_ATTRIBUTE);
        $token = $this->getToken($session, $guard);

        // get users secret key
        $key = $user->getSecretKey();

        $form  = new GoogleMfaForm($guard);
        $error = null;

        if ($request->getMethod() === 'POST') {
            // get user data from POST vars
            $form->setData($request->getParsedBody());
            if ($form->isValid()) {
                // get filtered form data
                $data = $form->getData();
                if (! is_array($data)) {
                    throw new Exception(sprintf(
                        'Invalid return type, expected array, got %s',
                        gettype($data)
                    ));
                }

                // get secret key and code from POST
                $key = $data['secret_key'];
                $pin = $data['pin'];

                if ($this->mfaService->isValidCode($key, $pin)) {
                    $session->unset('mfa:user:id');
                    $session->set(IdentityInterface::class, [
                        'id'    => $user->getId(),
                        'email' => $user->getEmail(),
                        'name'  => $user->getFullName(),
                    ]);
                    return new RedirectResponse($this->helper->generate('home'));
                }
            }

            $error = 'Invalid key';
            // regenerate token on failure
            $token = $this->getToken($session, $guard);
        }

        return new HtmlResponse($this->renderer->render('mfa::mfa-page', [
            'layout'     => 'layout::clean',
            'form'       => $form,
            'qrcode_url' => $this->mfaService->getQrCodeUrl($user->getEmail(), $key),
            'secret_key' => $key,
            'token'      => $token,
            'error'      => $error,
        ]));
    }
}
