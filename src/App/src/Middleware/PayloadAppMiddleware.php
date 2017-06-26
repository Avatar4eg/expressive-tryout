<?php
namespace App\Middleware;

use App\Service\StringService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PayloadAppMiddleware
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
            || !array_key_exists('status', $content)
            || !array_key_exists('links', $content)
            || !is_array($content['links'])) {
            return $response->withStatus(400);
        }

        $request->{'parsed_data'} = StringService::clearArray($content);

        return $out($request, $response);
    }
}