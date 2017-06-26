<?php
namespace App\Middleware;

use Interop\Container\ContainerInterface;

class PayloadCreateMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new PayloadCreateMiddleware();
    }
}