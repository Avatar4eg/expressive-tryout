<?php
namespace App\Service;

use App\Entity\Task;

class TaskService
{
    const TASK_STATUS_NEW       = 'new';
    const TASK_STATUS_PROCESS   = 'process';
    const TASK_STATUS_READY     = 'ready';
    const TASK_STATUS_ERROR     = 'error';
    const TASK_STATUS_CANCELED  = 'canceled';
    const TASK_STATUS_ARCHIVE   = 'archive';

    /**
     * @param Task $task
     * @param bool $as_array
     * @return mixed
     */
    public static function parseTask(Task $task, bool $as_array = false)
    {
        if ($as_array) {
            return [
                'task' => $task->getId(),
                'links' => $task->getLinks(),
                'status' => $task->getStatus(),
                'render' => $task->getLastRenderId()
            ];
        }
        $parsed_task = new \StdClass();
        $parsed_task->{'task'} = $task->getId();
        $parsed_task->{'links'} = $task->getLinks();
        $parsed_task->{'status'} = $task->getStatus();
        $parsed_task->{'render'} = $task->getLastRenderId();
        return $parsed_task;
    }
}