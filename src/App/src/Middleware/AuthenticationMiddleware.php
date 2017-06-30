<?php
namespace App\Middleware;

use App\Service\StringService;
use App\Entity\Client;
use Doctrine\ORM\EntityManager;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class AuthenticationMiddleware implements MiddlewareInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return \Psr\Http\Message\ResponseInterface|JsonResponse
     * @throws \InvalidArgumentException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $token = $request->getHeaderLine('Authorization');

        if (empty($token)) {
            return new JsonResponse([
                'success'   => false,
                'messages'  => [
                    'Token required',
                ]
            ], 401);
        }

        $client = $this->entityManager->getRepository(Client::class)->findOneBy([
            'token' => StringService::clearString($token, Client::TOKEN_LENGTH),
        ]);

        if (!$client) {
            return new JsonResponse([
                'success'   => false,
                'messages'  => [
                    'Auth error',
                ]
            ], 401);
        }

        return $delegate->process($request->withAttribute('client', $client));
    }
}