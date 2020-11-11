<?php

return (function () {
    $container = require 'config/container.php';
    $em = $container->get('doctrine.entity_manager.orm_default');
    return new \Symfony\Component\Console\Helper\HelperSet([
        'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
        'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em),
    ]);
})();
