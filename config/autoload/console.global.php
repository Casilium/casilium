<?php

declare(strict_types=1);

return [
    'console' => [
        'commands' => [
            App\Command\TestCommand::class,
            App\Command\CreateSodiumKey::class,
        ],
    ],
];
