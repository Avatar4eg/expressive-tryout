<?php
namespace App\Middleware;

use App\Service\StringService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class PayloadCreateMiddleware implements MiddlewareInterface
{
    /**
     * PayloadMiddleware constructor.
     */
    public function __construct()
    {

    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return \Psr\Http\Message\ResponseInterface|JsonResponse
     * @throws \InvalidArgumentException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $content = json_decode($request->getBody()->getContents(), true);

        if (!is_array($content)
            || !array_key_exists('task', $content)
            || !array_key_exists('template', $content)
            || !array_key_exists('render', $content)
            || !array_key_exists('data', $content)
            || !array_key_exists('callback_url', $content)) {
            return new JsonResponse([
                'success'   => false,
                'messages'  => [
                    'Bad payload',
                ]
            ], 400);
        }

        return $delegate->process($request->withAttribute('parsed_content', StringService::clearArray($content, ['callback_url', 'data'])));
    }
}