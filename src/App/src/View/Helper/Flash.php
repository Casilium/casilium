<?php
declare(strict_types=1);

namespace App\View\Helper;

use Mezzio\Session\Session;
use Mezzio\Flash\FlashMessages;
use Laminas\View\Helper\AbstractHelper;

class Flash extends AbstractHelper
{
    public function __invoke() : array
    {
        if (isset($_SESSION)) {
            $flashMessages = FlashMessages::createFromSession(
                new Session($_SESSION)
            );
            return $flashMessages->getFlashes();
        }

        return [];
    }
}
