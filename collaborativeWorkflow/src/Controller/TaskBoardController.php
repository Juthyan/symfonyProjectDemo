<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\TaskDto;
use App\Formatter\TaskFormatter;
use App\Services\TaskService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/tasks')]
final class TaskBoardController extends AbstractController
{
    private TaskService $taskService;
    private TaskFormatter $taskFormatter;

    public function __construct(TaskService $taskService, TaskFormatter $taskFormatter)
    {
        $this->taskService = $taskService;
        $this->taskFormatter = $taskFormatter;
    }

    #[Route('/{id}', name: 'get_tasks', methods: ['GET'])]
    public function fetchBoardTasks(int $id): Response
    {
        $data = $this->taskFormatter->formatTasks($this->taskService->fetchBoardTask($id));

        return new JsonResponse($data);
    }

    #[Route('/save', name: 'create_task', methods: ['POST'])]
    public function createTask(#[MapRequestPayload(validationGroups: ['create'])] TaskDto $dto): Response
    {
        return $this->taskService->createTask($dto);
    }

    #[Route('/edit/{id}', name: 'edit_task', methods: ['PATCH'])]
    public function editTask(int $id, #[MapRequestPayload] TaskDto $dto): Response
    {
        return $this->taskService->editTask($id, $dto);
    }

    #[Route('/{id}', name: 'delete_task', methods: ['DELETE'])]
    public function deleteTask(int $id): Response
    {
        return $this->taskService->deleteTask($id);
    }
}
