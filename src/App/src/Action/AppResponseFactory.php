<?php
namespace App\Action;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class AppResponseFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $entityManager = $container->has(EntityManager::class) ? $container->get(EntityManager::class) : null;

        return new AppResponseAction($config, $entityManager);
    }
}
