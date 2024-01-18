<?php

declare(strict_types=1);

namespace App\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Mezzio\Flash\FlashMessages;
use Mezzio\Session\Session;

class Flash extends AbstractHelper
{
    public function __invoke(): array
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
