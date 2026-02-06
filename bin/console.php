#!/usr/local/bin/php
<?php
chdir(__DIR__ . '/../');

define('ROOT', getcwd());

require 'vendor/autoload.php';

use Symfony\Component\Console\Application;

/** @var \Interop\Container\ContainerInterface $container */
$container = require 'config/container.php';
$application = new Application('Titan Console');

$commands = $container->get('config')['console']['commands'];
foreach ($commands as $command) {
    try {
        $application->add($container->get($command));
    } catch (Throwable $e) {
        fwrite(
            STDERR,
            sprintf(
                "Skipped console command %s: %s (check config/autoload/mail.local.php if this is mail related)\n",
                $command,
                $e->getMessage()
            )
        );
    }
}

$application->run();
