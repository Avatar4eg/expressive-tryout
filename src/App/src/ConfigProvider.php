<?php
namespace App;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.zendframework.com/zend-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates'    => $this->getTemplates(),
            'routes'       => $this->getRoutes(),
        ];
    }

    /**
     * Returns the container dependencies
     *
     * @return array
     */
    public function getDependencies(): array
    {
        return [
            'invokables' => [

            ],
            'factories'  => [
                Action\HomePageAction::class    => Action\HomePageFactory::class,
                Action\TaskCreateAction::class  => Action\TaskCreateFactory::class,
                Action\TaskModifyAction::class  => Action\TaskModifyFactory::class,
                Action\TaskGetAction::class     => Action\TaskGetFactory::class,
                Action\AppResponseAction::class => Action\AppResponseFactory::class,
            ],
        ];
    }

    /**
     * Returns the templates configuration
     *
     * @return array
     */
    public function getTemplates(): array
    {
        return [
            'paths' => [
                'app'           => [__DIR__ . '/../templates/app'],
                'error'         => [__DIR__ . '/../templates/error'],
                'layout'        => [__DIR__ . '/../templates/layout'],
                'layout/part'   => [__DIR__ . '/../templates/layout/part'],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getRoutes(): array
    {
        return [
            [
                'name'            => 'api.task.create',
                'path'            => '/api/task',
                'middleware'      => [
                    Middleware\AuthenticationMiddleware::class,
                    Middleware\ConfigMiddleware::class,
                    Middleware\PayloadCreateMiddleware::class,
                    Action\TaskCreateAction::class,
                ],
                'allowed_methods' => ['POST'],
            ],
            [
                'name'            => 'api.task.modify',
                'path'            => '/api/task/[:task_id]/[:action]',
                'middleware'      => [
                    Middleware\AuthenticationMiddleware::class,
                    Middleware\ConfigMiddleware::class,
                    Middleware\PayloadModifyMiddleware::class,
                    Action\TaskModifyAction::class,
                ],
                'allowed_methods' => ['PATCH'],
            ],
            [
                'name'            => 'api.task.get.list',
                'path'            => '/api/task',
                'middleware'      => [
                    Middleware\AuthenticationMiddleware::class,
                    Action\TaskGetAction::class,
                ],
                'allowed_methods' => ['GET'],
            ],
            [
                'name'            => 'api.task.get.one',
                'path'            => '/api/task/[:task_id]',
                'middleware'      => [
                    Middleware\AuthenticationMiddleware::class,
                    Action\TaskGetAction::class,
                ],
                'allowed_methods' => ['GET'],
            ],
            [
                'name'            => 'api.response',
                'path'            => '/api/response/[:task_id]',
                'middleware'      => [
                    Middleware\AuthenticationMiddleware::class,
                    Middleware\PayloadAppMiddleware::class,
                    Action\AppResponseAction::class,
                ],
                'allowed_methods' => ['POST'],
            ],
        ];
    }
}
