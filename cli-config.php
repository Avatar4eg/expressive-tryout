<?php
$container = require __DIR__ . '/config/container.php';

return new \Symfony\Component\Console\Helper\HelperSet([
    'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper(
        $container->get(\Doctrine\ORM\EntityManager::class)
    ),
]);