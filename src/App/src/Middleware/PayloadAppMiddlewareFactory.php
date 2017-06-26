<?php
namespace App\Middleware;

use Interop\Container\ContainerInterface;

class PayloadAppMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new PayloadAppMiddleware();
    }
}