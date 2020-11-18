<?php
declare(strict_types=1);

namespace Mfa\Handler;

use App\Traits\CsrfTrait;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Authentication\UserInterface;
use Mezzio\Csrf\CsrfMiddleware;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Flash\FlashMessagesInterface;
use Mezzio\Helper\UrlHelper;
use Mezzio\Session\Session;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Template\TemplateRendererInterface;
use Mfa\Form\GoogleMfaForm;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sonata\GoogleAuthenticator\GoogleAuthenticator;
use Sonata\GoogleAuthenticator\GoogleQrUrl;
use User\Entity\User;
use function array_key_exists;
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

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var UrlHelper */
    private $helper;

    /** @var TemplateRendererInterface */
    private $renderer;

    /** @var array */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TemplateRendererInterface $renderer,
        UrlHelper $helper,
        array $config
    ) {
        $this->entityManager = $entityManager;
        $this->renderer      = $renderer;
        $this->helper        = $helper;
        $this->config        = $config;

        if (! array_key_exists('issuer', $this->config)) {
            throw new Exception('mfa issuer key not found in configuration');
        }
    }

    /**
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var Session $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        // if UserInterface exists in session, user is already logged in, redirect to home page.
        if (! $session->has(UserInterface::class)) {
            return new RedirectResponse($this->helper->generate('home'));
        }

        // get csrf token
        $guard = $request->getAttribute(CsrfMiddleware::GUARD_ATTRIBUTE);
        $token = $this->getToken($session, $guard);

        // get user from session
        $user = $session->get(UserInterface::class);

        // get user id
        $user_id = $user['details']['id'] ?? null;
        if (! $user_id) {
            throw new Exception('User ID not found');
        }

        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->find($user_id);
        if ($user == null) {
            throw new Exception('User not found');
        }

        // retrieve users stored secret key
        $secret_key = $user->getSecretKey();

        $authenticator = new GoogleAuthenticator();
        if ($secret_key == null) {
            // user doesn't have a key generate one and save
            $secret_key = $authenticator->generateSecret();
            $user->setSecretKey($secret_key);
            $this->entityManager->flush();
        }

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
                $secret_key = $data['secret_key'];
                $pin        = $data['pin'];

                $authenticator = new GoogleAuthenticator();
                // verify code
                if ($authenticator->checkCode($secret_key, $pin)) {
                    // enable mfa and save to db
                    $user->setMfaEnabled(true);
                    $this->entityManager->flush();

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

        // generate google url
        $qrcode_url = GoogleQrUrl::generate($user->getEmail(), $secret_key, $this->config['issuer']);
        return new HtmlResponse($this->renderer->render('mfa::mfa-page', [
            'form'       => $form,
            'qrcode_url' => $qrcode_url,
            'secret_key' => $secret_key,
            'token'      => $token,
            'error'      => $error,
        ]));
    }
}
