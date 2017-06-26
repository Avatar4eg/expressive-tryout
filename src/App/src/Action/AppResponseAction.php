<?php
namespace App\Action;

use App\Entity\Client;
use App\Entity\Task;
use App\Service\RoleService;
use App\Service\StringService;
use App\Service\TaskService;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Exception\ClientException;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use GuzzleHttp\Client as GuzzleClient;

class AppResponseAction implements ServerMiddlewareInterface
{
    /**
     * @var array $config
     */
    private $config;

    /**
     * @var EntityManager $entityManager
     */
    private $entityManager;

    /**
     * @var Task|null $task
     */
    private $task;

    /**
     * TaskSetupAction constructor.
     * @param array $config
     * @param EntityManager $entityManager
     */
    public function __construct(array $config, EntityManager $entityManager)
    {
        $this->config = $config;
        $this->entityManager = $entityManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return JsonResponse
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): JsonResponse
    {
        $data = $request{'parsed_data'};

        /** @var Client $client */
        $client = $request->{'client'};

        $client_role = $client->getRole();
        if ($client_role !== RoleService::ROLE_APP || $client_role !== RoleService::ROLE_ADMIN) {
            return new JsonResponse([
                'success'   => false,
                'messages'  => [
                    'Not allowed',
                ]
            ], 403);
        }

        $task_id = $request->getAttribute('task_id');
        $this->task = $this->entityManager->getRepository(Task::class)->findOneBy([
            'id' => StringService::clearString($task_id),
        ]);
        if ($this->task === null) {
            return new JsonResponse([
                'success'   => false,
                'messages'  => [
                    'Task not found',
                ]
            ], 404);
        }

        $links = $this->task->getLinks();
        foreach ($data['links'] as $link) {
            $links[] = $link;
        }
        $this->task->setStatus($data['status'])
            ->setLinks($links);
        $this->entityManager->persist($this->task);
        $this->entityManager->flush();

        if ($data['status'] === TaskService::TASK_STATUS_READY && $this->task->getClient() !== null) {
            $url = $this->task->getCallbackUrl() ?: $this->task->getClient()->getApiUrl() . '/' . $this->task->getId();
            $config = [
                'url'       => $url,
                'params'    => $this->task->getClient()->getApiParams()
            ];
            $client_data = [
                'success'   => true,
                'content'   => TaskService::parseTask($this->task, true),
                'messages'  => [
                    'Render ready'
                ]
            ];
            if (!$this->requestToClient($client_data, $config)) {
                return new JsonResponse([
                    'success'   => false,
                    'messages'  => [
                        'Client not responding correctly',
                    ]
                ], 404);
            }
        }

        return new JsonResponse([
            'success'   => true,
            'messages'  => [
                'Task status successfully updated',
            ]
        ], 201);
    }

    /**
     * @param array $data
     * @param array $config
     * @return bool
     */
    protected function requestToClient(array $data, array $config): bool
    {
        $client = new GuzzleClient([
            'verify' => false,
        ]);
        try {
            $response = $client->post($config['url'], [
                'headers'   => $config['params'],
                'body'      => $data,
            ]);
            return $response->getStatusCode() === 200;
        } catch (ClientException $e) {
            return false;
        }
    }

}
