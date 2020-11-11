<?php
declare(strict_types=1);

namespace App\Traits;

use Mezzio\Csrf\SessionCsrfGuard;
use Mezzio\Session\SessionInterface;

/**
 * Helper function for CSRF function
 *
 * CSRF helper function for use within forms, defined in the main application as likely
 * to be used by multiple forms.
 *
 * @package App\Traits
 */
trait CsrfTrait
{
    /**
     * Returns CSRF token for use in forms to prevent XSS attacks
     *
     * @param SessionInterface $session
     * @param SessionCsrfGuard $guard
     * @return string
     */
    private function getToken(SessionInterface $session, SessionCsrfGuard $guard): string
    {
        if (! $session->has('__csrf')) {
            return $guard->generateToken();
        }

        return $session->get('__csrf');
    }
}
