<?php
namespace App\Middleware;

use App\Service\FileService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class ConfigMiddleware implements MiddlewareInterface
{
    /**
     * @var array $config
     */
    private $config;

    /**
     * ConfigMiddleware constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if (!array_key_exists('filepath', $this->config)
            || !array_key_exists('work_path', $this->config['filepath'])
            || !array_key_exists('editor_path', $this->config['filepath'])
            || !array_key_exists('editor_params', $this->config['filepath'])
            || !array_key_exists('run_local', $this->config['filepath'])
            || !array_key_exists('run_remote', $this->config['filepath'])
            || !FileService::checkFileExist($this->config['filepath']['work_path'])
            || !FileService::checkFileExist($this->config['filepath']['editor_path'])) {
            return new JsonResponse([
                'success'   => false,
                'messages'  => [
                    'Config error',
                ]
            ], 500);
        }

        return $delegate->process($request);
    }
}