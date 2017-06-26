<?php
namespace App\Middleware;

use App\Service\StringService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PayloadCreateMiddleware
{
    /**
     * PayloadMiddleware constructor.
     */
    public function __construct()
    {

    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $out)
    {
        $content = json_decode($request->getBody()->getContents(), true);

        if (!is_array($content)
            || !array_key_exists('task', $content)
            || !array_key_exists('template', $content)
            || !array_key_exists('render', $content)
            || !array_key_exists('data', $content)
            || !array_key_exists('callback_url', $content)) {
            return $response->withStatus(400);
        }

        $request->{'parsed_data'} = StringService::clearArray($content, ['callback_url', 'data']);

        return $out($request, $response);
    }
}