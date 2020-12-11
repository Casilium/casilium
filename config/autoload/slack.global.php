<?php

declare(strict_types=1);

return [
    'slack' => [
        // The channel to post to, channel can either be a #channel, #group or @username. Set to null
        // to use webhook defaults
        'channel' => '#support',

        // The username we should post as, set to ull to use the default set on slack webhook.
        'username' => 'Casilium',

        // The default icon to use, this can be either a URL to an image or a slack emoji. Set to null
        // to use default webhook settings.
        'icon' => null,

        // Whether names like @user should be converted into links by slack
        'link_names' => false,

        // Whether slack should unfurl links to text-based content
        'unfurl_links' => false,

        // Whether slack should unfurl links to media content such as images and YouTube videos
        'unfurl_media' => false,

        // Whether message text should be interpreted in Slack's markdown language.
        'allow_markdown' => true,

        // Which attachment fields should be interpreted in Slack's markdown language. By default
        // slack assumes no fields in an attachment should be formatted as markdown.
        'markdown_in_attachments' => [],
    ],
];
