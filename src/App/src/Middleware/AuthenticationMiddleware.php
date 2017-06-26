<?php
namespace App\Middleware;

use App\Service\StringService;
use App\Entity\Client;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthenticationMiddleware
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

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $out)
    {
        $token = $request->getHeaderLine('Authorization');

        if (empty($token)) {
            return $response->withStatus(401);
        }

        $client = $this->entityManager->getRepository(Client::class)->findOneBy([
            'token' => StringService::clearString($token, Client::TOKEN_LENGTH),
        ]);

        if (!$client) {
            return $response->withStatus(401);
        }

        $request->{'client'} = $client;

        return $out($request, $response);
    }
}