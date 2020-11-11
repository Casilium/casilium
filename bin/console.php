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
    $application->add($container->get($command));
}

$application->run();