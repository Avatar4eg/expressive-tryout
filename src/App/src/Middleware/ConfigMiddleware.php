<?php
namespace App\Middleware;

use App\Service\FileService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ConfigMiddleware
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

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $out)
    {
        if (!array_key_exists('filepath', $this->config)
            || array_key_exists('work_path', $this->config['filepath'])
            || array_key_exists('editor_path', $this->config['filepath'])
            || array_key_exists('editor_params', $this->config['filepath'])
            || array_key_exists('run_local', $this->config['filepath'])
            || array_key_exists('run_remote', $this->config['filepath'])
            || !FileService::checkFileExist($this->config['filepath']['work_path'])
            || !FileService::checkFileExist($this->config['filepath']['editor_path'])) {
            return $response->withStatus(500);
        }

        return $out($request, $response);
    }
}