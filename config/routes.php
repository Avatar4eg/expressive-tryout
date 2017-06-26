<?php
/**
 * Setup routes with a single request method:
 *
 * $app->get('/', App\Action\HomePageAction::class, 'home');
 * $app->post('/album', App\Action\AlbumCreateAction::class, 'album.create');
 * $app->put('/album/:id', App\Action\AlbumUpdateAction::class, 'album.put');
 * $app->patch('/album/:id', App\Action\AlbumUpdateAction::class, 'album.patch');
 * $app->delete('/album/:id', App\Action\AlbumDeleteAction::class, 'album.delete');
 *
 * Or with multiple request methods:
 *
 * $app->route('/contact', App\Action\ContactAction::class, ['GET', 'POST', ...], 'contact');
 *
 * Or handling all request methods:
 *
 * $app->route('/contact', App\Action\ContactAction::class)->setName('contact');
 *
 * or:
 *
 * $app->route(
 *     '/contact',
 *     App\Action\ContactAction::class,
 *     Zend\Expressive\Router\Route::HTTP_METHOD_ANY,
 *     'contact'
 * );
 *
 * @var \Zend\Expressive\Application $app
 */

$app->get('/', App\Action\HomePageAction::class, 'home');
$app->post('/api/task', [
    App\Middleware\AuthenticationMiddleware::class,
    App\Middleware\ConfigMiddleware::class,
    App\Middleware\PayloadCreateMiddleware::class,
    App\Action\TaskCreateAction::class,
], 'api.task.create');
//$app->patch('/api/task/[:task_id]/[:action]', [
//    App\Middleware\AuthenticationMiddleware::class,
//    App\Middleware\ConfigMiddleware::class,
//    App\Middleware\PayloadModifyMiddleware::class,
//    App\Action\TaskModifyAction::class,
//], 'api.task.modify')
//    ->setOptions([
//        'tokens' => ['id' => '\d+'],
//    ]);
//$app->get('/api/task[/:task_id]', [
//    App\Middleware\AuthenticationMiddleware::class,
//    App\Action\TaskGetAction::class,
//], 'api.task.get')
//    ->setOptions([
//        'tokens' => ['id' => '\d+'],
//    ]);
//$app->post('/api/response/[:task_id]', [
//    App\Middleware\AuthenticationMiddleware::class,
//    App\Middleware\PayloadAppMiddleware::class,
//    App\Action\AppResponseAction::class,
//], 'api.response')
//    ->setOptions([
//        'tokens' => ['id' => '\d+'],
//    ]);