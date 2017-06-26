<?php
namespace App\Middleware;

use App\Service\StringService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PayloadModifyMiddleware
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
            || !array_key_exists('render', $content)) {
            return $response->withStatus(400);
        }

        $request->{'parsed_data'} = $this->clearContent($content);

        return $out($request, $response);
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