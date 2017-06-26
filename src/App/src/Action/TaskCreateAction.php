<?php
namespace App\Action;

use App\Entity\Client;
use App\Entity\Task;
use App\Entity\Template;
use App\Service\EditorService;
use App\Service\FileService;
use App\Service\TaskService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

class TaskCreateAction implements ServerMiddlewareInterface
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
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): JsonResponse
    {
        /** @var Client $client */
        $client = $request->{'client'};
        $data = $request->{'parsed_data'};

        /** @var Template|null $template */
        $template = $this->entityManager->getRepository(Template::class)->find($data['template']);
        if ($template === null) {
            return new JsonResponse([
                'success'   => false,
                'messages'  => [
                    'Template not found',
                ]
            ], 404);
        }

        $this->task = new Task($client, $data['task'], $template);
        $this->task->setCallbackUrl($data['callback_url'])
            ->setParams($data['data'])
            ->setLastRenderId($data['render']);

        $this->entityManager->persist($this->task);
        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            return new JsonResponse([
                'success'   => false,
                'messages'  => [
                    'Task already exists or cant create task',
                ]
            ], 500);
        }

        if (!$this->entityManager->contains($this->task)) {
            return new JsonResponse([
                'success'   => false,
                'messages'  => [
                    'Error saving task',
                ]
            ], 500);
        }

        $this->task->setPath(implode(DIRECTORY_SEPARATOR, [
            realpath($this->config['filepath']['work_path']),
            $this->task->getId()
        ]));

        $editor_result = false;
        if ($this->config['filepath']['run_local']) {
            if (!$this->copyResources()) {
                $this->task->setStatus(TaskService::TASK_STATUS_ERROR);
                $this->entityManager->persist($this->task);
                $this->entityManager->flush();
                return new JsonResponse([
                    'success'   => false,
                    'messages'  => [
                        'Error getting resources',
                    ]
                ], 500);
            }
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
                'data' => $data['data']
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
            'task_id'   => $this->task->getId(),
            'messages'  => [
                'Task successfully created',
            ]
        ], 201);
    }

    /**
     * @return bool
     */
    protected function copyResources(): bool
    {
        if (!@mkdir($this->task->getPath(), 0777, true) && !is_dir($this->task->getPath())) {
            return false;
        }
        foreach ($this->task->getTemplate()->getPaths() as $path) {
            if (!FileService::checkFileExist($path)
                || !copy($path, $this->task->getPath())) {
                return false;
            }
        }
        return true;
    }
}
