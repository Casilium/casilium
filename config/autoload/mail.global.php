<?php

declare(strict_types=1);

return [
// Global mail settings
    'mail' => [
        // ignore mail with following subjects (regex)
        'ignore_with_subject' => [
            '/^(\W*|\s*)?Automatic Reply*/i',
            '/^(\W*|\s*)?Out of Office*/i',
            '/^(\W*|\s*)?Undeliverable:*/i',
            '/^(\W*|\s*)?Undelivered Mail*/i',
            '/^(\W*|\s*)?Mail Delivery System*/i',
            '/(?i:^.*(- \[SPAM\]|- Virus found).*$)/i',
        ],
        // ignore mail with the following body matches (regex)
        'ignore_with_body' => [
            '/reacted to your message/i',
        ],
    ],
];
