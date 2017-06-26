<?php
namespace App\Middleware;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class AuthenticationMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $entityManager = $container->has(EntityManager::class) ? $container->get(EntityManager::class) : null;

        return new AuthenticationMiddleware($entityManager);
    }
}