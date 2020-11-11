<?php
declare(strict_types=1);

namespace Mfa\Handler;

use App\Traits\CsrfTrait;

use Doctrine\ORM\EntityManagerInterface;
use Mfa\Form\GoogleMfaForm;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use User\Entity\User;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Authentication\UserInterface;
use Mezzio\Csrf\CsrfMiddleware;
use Mezzio\Helper\UrlHelper;
use Mezzio\Session\Session;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Template\TemplateRendererInterface;
use Sonata\GoogleAuthenticator\GoogleAuthenticator;
use Sonata\GoogleAuthenticator\GoogleQrUrl;

/**
 * Handles MFA on user login
 *
 * @package Mfa\Handler
 */
class ValidateMfaHandler implements MiddlewareInterface
{
    use CsrfTrait;

    /**
     * @var StorageInterface
     */
    private $cache;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UrlHelper
     */
    private $helper;

    /**
     * @var TemplateRendererInterface
     */
    private $renderer;

    /**
     * @var array
     */
    private $config;

    /**
     * ValidateMfaHandler constructor.
     *
     * @param StorageInterface $cache
     * @param EntityManagerInterface $entityManager
     * @param TemplateRendererInterface $renderer
     * @param UrlHelper $helper
     * @param array $config
     * @throws \Exception
     */
    public function __construct(
        StorageInterface $cache,
        EntityManagerInterface $entityManager,
        TemplateRendererInterface $renderer,
        UrlHelper $helper,
        array $config
    ) {
        $this->cache = $cache;
        $this->entityManager = $entityManager;
        $this->renderer = $renderer;
        $this->helper = $helper;
        $this->config = $config;

        if (! array_key_exists('issuer', $this->config)) {
            throw new \Exception('mfa issuer key not found in configuration');
        }

    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \Exception
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
        $user_id = (int)$session->get('mfa:user:id');

        // check if user is cached and retrieve user from cache
        $cache_key = 'auth:cached_user:' . $user_id;
        if ($this->cache->hasItem($cache_key) == false) {
            throw new \Exception('User not found in cache!');
        }
        $cached_user = $this->cache->getItem($cache_key);

        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->find($user_id);
        if ($user == null) {
            throw new \Exception('User not found');
        }

        // generate csrf
        $guard = $request->getAttribute(CsrfMiddleware::GUARD_ATTRIBUTE);
        $token = $this->getToken($session, $guard);

        // get users secret key
        $secret_key = $user->getSecretKey();

        $form = new GoogleMfaForm($guard);
        $error = null;
        if ($request->getMethod() === 'POST') {
            // get user data from POST vars
            $form->setData($request->getParsedBody());
            if ($form->isValid()) {
                // get filtered form data
                $data = $form->getData();
                if (! is_array($data)) {
                    throw new \Exception(sprintf(
                        'Invalid return type, expected array, got %s',
                        gettype($data)
                    ));
                }

                // get secret key and code from POST
                $secret_key = $data['secret_key'];
                $pin = $data['pin'];

                // verify code/pin
                $authenticator = new GoogleAuthenticator();
                if ($authenticator->checkCode($secret_key, $pin)) {
                    // remove mfa user id from session
                    $session->unset('mfa:user:id');
                    // restore the user object to session from cache
                    $session->set(UserInterface::class, $cached_user);
                    return new RedirectResponse($this->helper->generate('home'));
                }
            }
            $error = 'Invalid key';
            // regenerate token on falure
            $token = $this->getToken($session, $guard);
        }

        // generate google url
        $qrcode_url = GoogleQrUrl::generate($user->getEmail(), $secret_key, $this->config['issuer']);
        return new HtmlResponse($this->renderer->render('mfa::mfa-page', [
            'layout'     => 'layout::clean',
            'form'       => $form,
            'qrcode_url' => $qrcode_url,
            'secret_key' => $secret_key,
            'token'      => $token,
            'error'      => $error,
        ]));
    }
}
