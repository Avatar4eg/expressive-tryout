<?php
namespace App\Action;

use App\Entity\Client;
use App\Entity\Task;
use App\Service\RoleService;
use App\Service\StringService;
use App\Service\TaskService;
use Doctrine\ORM\EntityManager;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

class TaskGetAction implements ServerMiddlewareInterface
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
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): JsonResponse
    {
        $task_id = $request->getAttribute('task_id', false);
        if ($task_id) {
            return $this->getItemAction($task_id, $request, $delegate);
        }
        return $this->getListAction($request, $delegate);
    }

    /**
     * @param int $id
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return JsonResponse
     * @throws \InvalidArgumentException
     */
    public function getItemAction(int $id, ServerRequestInterface $request, DelegateInterface $delegate): JsonResponse
    {
        /** @var Client $client */
        $client = $request->getAttribute('client');

        $params = [
            'id' => $id
        ];
        if ($client->getRole() !== RoleService::ROLE_ADMIN) {
            $params['client'] = $client;
        }

        /** @var Task|null $task */
        $task = $this->entityManager->getRepository(Task::class)->findOneBy($params);
        if ($task === null) {
            return new JsonResponse([
                'success'   => false,
                'messages'  => [
                    'Task not found',
                ]
            ], 404);
        }

        $data = TaskService::parseTask($task);

        return new JsonResponse([
            'success'   => true,
            'content'   => [
                'task'  => $data,
            ],
            'messages'  => [
                'Successfully fetched',
            ]
        ], 200);
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return JsonResponse
     * @throws \InvalidArgumentException
     */
    public function getListAction(ServerRequestInterface $request, DelegateInterface $delegate): JsonResponse
    {
        /** @var Client $client */
        $client = $request->{'client'};
        $params = [];
        if ($client->getRole() !== RoleService::ROLE_ADMIN) {
            $params['client'] = $client;
        }

        $query = $request->getQueryParams();
        if (array_key_exists('status', $query)) {
            $params[] = StringService::clearString($query['status']);
        }

        /** @var Task|null $task */
        $tasks = $this->entityManager->getRepository(Task::class)->findBy($params);

        $data = [];
        foreach ($tasks as $task) {
            $data[] = TaskService::parseTask($task);
        }

        return new JsonResponse([
            'success'   => true,
            'content'   => [
                'tasks' => $data,
            ],
            'messages'  => [
                'Successfully fetched',
            ]
        ], 200);
    }
}
