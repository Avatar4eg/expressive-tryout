<?php
namespace App\Middleware;

use Interop\Container\ContainerInterface;

class PayloadModifyMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new PayloadModifyMiddleware();
    }
}