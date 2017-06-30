<?php
namespace App\Middleware;

use App\Service\StringService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class PayloadModifyMiddleware implements MiddlewareInterface
{
    /**
     * PayloadMiddleware constructor.
     */
    public function __construct()
    {

    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $content = json_decode($request->getBody()->getContents(), true);

        if (!is_array($content)
            || !array_key_exists('render', $content)) {
            return new JsonResponse([
                'success'   => false,
                'messages'  => [
                    'Bad payload',
                ]
            ], 400);
        }

        return $delegate->process($request->withAttribute('parsed_content', StringService::clearArray($content)));
    }

    protected function clearContent(array $content): array
    {
        foreach ($content as $key => $item) {
            if (!is_array($item)) {
                $content[$key] = StringService::clearString($item);
            } else {
                $content[$key] = $this->clearContent($item);
            }
        }
        return $content;
    }
}