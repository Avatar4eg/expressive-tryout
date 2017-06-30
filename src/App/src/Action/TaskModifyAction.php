<?php
namespace App\Action;

use App\Entity\Client;
use App\Entity\Task;
use App\Service\EditorService;
use App\Service\FileService;
use App\Service\RoleService;
use App\Service\TaskService;
use Doctrine\ORM\EntityManager;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

class TaskModifyAction implements ServerMiddlewareInterface
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
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): JsonResponse
    {
        /** @var Client $client */
        $client = $request->getAttribute('client');

        $task_id = $request->getAttribute('task_id');
        $params = [
            'id' => $task_id
        ];
        if ($client->getRole() !== RoleService::ROLE_ADMIN) {
            $params['client'] = $client;
        }

        $this->task = $this->entityManager->getRepository(Task::class)->findOneBy($params);
        if ($this->task === null) {
            return new JsonResponse([
                'success'   => false,
                'messages'  => [
                    'Task not found',
                ]
            ], 404);
        }

        $action = $request->getAttribute('action', 'index') . 'Action';
        if (!method_exists($this, $action)) {
            return new JsonResponse([
                'success'   => false,
                'messages'  => [
                    'Action not found',
                ]
            ], 404);
        }

        return $this->$action($request, $delegate);
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
    public function runAction(ServerRequestInterface $request, DelegateInterface $delegate): JsonResponse
    {
        /** @var array $data */
        $data = $request->getAttribute('parsed_content');

        $this->task->setLastRenderId($data['render']);
        $this->entityManager->persist($this->task);
        $this->entityManager->flush();

        $editor_result = false;
        if ($this->config['filepath']['run_local']) {
            $json_path = implode(DIRECTORY_SEPARATOR, [
                $this->task->getPath(),
                'data.json'
            ]);
            $json_data = [
                'render' => $data['render'],
                'data' => $data['data']
            ];
            if (!FileService::saveJson($json_data, $json_path)) {
                $this->task->setStatus(TaskService::TASK_STATUS_ERROR);
                $this->entityManager->persist($this->task);
                $this->entityManager->flush();
                return new JsonResponse([
                    'success'   => false,
                    'messages'  => [
                        'Error saving task data',
                    ]
                ], 500);
            }
            $local_data = [
                sprintf('"%s"', implode(DIRECTORY_SEPARATOR, [
                    $this->task->getPath(),
                    'script.jsx'
                ]))
            ];
            $editor_result = EditorService::runAppLocal($local_data, $this->config);
        }

        if ($this->config['filepath']['run_remote']) {
            $remote_data = [
                'render' => $data['render'],
                'data' => $this->task->getParams()
            ];
            $editor_result = EditorService::runAppRemote($remote_data, $this->config);
        }

        if (!$editor_result) {
            $this->task->setStatus(TaskService::TASK_STATUS_ERROR);
            $this->entityManager->persist($this->task);
            $this->entityManager->flush();
            return new JsonResponse([
                'success'   => false,
                'messages'  => [
                    'Error running app',
                ]
            ], 500);
        }

        $this->task->setStatus(TaskService::TASK_STATUS_PROCESS);
        $this->entityManager->persist($this->task);
        $this->entityManager->flush();

        return new JsonResponse([
            'success'   => true,
            'messages'  => [
                'Successfully running',
            ]
        ], 200);
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     */
    public function deleteAction(ServerRequestInterface $request, DelegateInterface $delegate): JsonResponse
    {
        $task_folder = implode(DIRECTORY_SEPARATOR, [
            $this->config['filepath']['work_path'],
            $this->task->getId()
        ]);
        if (!FileService::deleteFolder($task_folder)) {
            $this->task->setStatus(TaskService::TASK_STATUS_ERROR);
            $this->entityManager->persist($this->task);
            $this->entityManager->flush();
            return new JsonResponse([
                'success'   => false,
                'messages'  => [
                    'Error deleting task',
                ]
            ], 500);
        }

        $this->task->setStatus(TaskService::TASK_STATUS_ARCHIVE);
        $this->entityManager->persist($this->task);
        $this->entityManager->flush();

        return new JsonResponse([
            'success'   => true,
            'messages'  => [
                'Successfully deleted',
            ]
        ], 200);
    }
}
