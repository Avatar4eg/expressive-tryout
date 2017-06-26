<?php
namespace App\Middleware;

use Interop\Container\ContainerInterface;

class ConfigMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->has('config') ? $container->get('config') : [];

        return new ConfigMiddleware($config);
    }
}