<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\TaskDto;
use App\Entity\Task;
use App\Repository\BoardRepository;
use App\Repository\TaskRepository;
use App\Repository\TaskStateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class TaskService
{
    private TaskRepository $taskRepository;
    private TaskStateRepository $taskStateRepository;
    private BoardRepository $boardRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(TaskRepository $taskRepository, EntityManagerInterface $entityManager, TaskStateRepository $taskStateRepository, BoardRepository $boardRepository)
    {
        $this->taskRepository = $taskRepository;
        $this->entityManager = $entityManager;
        $this->taskStateRepository = $taskStateRepository;
        $this->boardRepository = $boardRepository;
    }

    public function fetchBoardTask(int $boardId): array
    {
        return $this->taskRepository->findBy(['board' => $boardId]);
    }

    public function createTask(TaskDto $dto): JsonResponse
    {
        $boardTask = new Task();

        $boardTask->setName($dto->getName());
        $boardTask->setDescription($dto->getDescription());

        $board = $this->boardRepository->findOneBy(['id' => $dto->getBoardId()]);
        $boardTask->setBoard($board);

        // Default State
        $defaultState = $this->taskStateRepository->findOneBy(['order' => 1]);
        $boardTask->setState($defaultState);

        try {
            $this->saveTask($boardTask);
        } catch (\Throwable $e) {
            return new JsonResponse(['status' => 'Creation failed: '.$e->getMessage()], 500);
        }

        return new JsonResponse(['status' => 'Task created'], 200);
    }

    public function editTask(int $id, TaskDto $dto): JsonResponse
    {
        $boardTask = $this->taskRepository->findOneBy(['id' => $id]);

        if (!$boardTask) {
            return new JsonResponse(['status' => 'Task not found'], 404);
        }

        if (!empty($dto->getName()) && $dto->getName() !== $boardTask->getName()) {
            $boardTask->setName($dto->getName());
        }

        if ($dto->getDescription() !== $boardTask->getDescription()) {
            $boardTask->setDescription($dto->getDescription());
        }

        if ($dto->getStateId() !== $boardTask->getState()->getId()) {
            $taskState = $this->taskStateRepository->findOneBy(['id' => $dto->getStateId()]);
            $boardTask->setState($taskState);
        }

        try {
            $this->saveTask($boardTask);
        } catch (\Throwable $e) {
            return new JsonResponse(['status' => 'Update failed: '.$e->getMessage()], 500);
        }

        return new JsonResponse(['status' => 'Task updated'], 200);
    }

    public function deleteTask(int $id): JsonResponse
    {
        $boardTask = $this->taskRepository->findOneBy(['id' => $id]);

        if (!$boardTask) {
            return new JsonResponse(['status' => 'Task not found'], 404);
        }
        try {
            $this->entityManager->remove($boardTask);
            $this->entityManager->flush();
        } catch (\Throwable $e) {
            return new JsonResponse(['status' => 'Delete task failed '.$e->getMessage()], 500);
        }

        return new JsonResponse(['status' => 'Task deleted'], 204);
    }

    private function saveTask(Task $task): void
    {
        try {
            $this->entityManager->beginTransaction();
            $this->entityManager->persist($task);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }
}
