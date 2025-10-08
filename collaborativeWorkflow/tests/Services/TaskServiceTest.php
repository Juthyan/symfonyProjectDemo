<?php

declare(strict_types=1);

namespace App\Tests\Services;

use App\DTO\TaskDto;
use App\Entity\Board;
use App\Entity\Task;
use App\Entity\TaskState;
use App\Repository\BoardRepository;
use App\Repository\TaskRepository;
use App\Repository\TaskStateRepository;
use App\Services\TaskService;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class TaskServiceTest extends MockeryTestCase
{
    private $taskRepositoryMock;
    private $taskStateRepositoryMock;
    private $boardRepositoryMock;
    private $entityManagerMock;
    private TaskService $service;

    protected function setUp(): void
    {
        $this->taskRepositoryMock = \Mockery::mock(TaskRepository::class);
        $this->taskStateRepositoryMock = \Mockery::mock(TaskStateRepository::class);
        $this->boardRepositoryMock = \Mockery::mock(BoardRepository::class);
        $this->entityManagerMock = \Mockery::mock(EntityManagerInterface::class);

        $this->service = new TaskService(
            $this->taskRepositoryMock,
            $this->entityManagerMock,
            $this->taskStateRepositoryMock,
            $this->boardRepositoryMock
        );
    }

    public function testFetchBoardTaskReturnsTasks(): void
    {
        $boardId = 1;
        $mockTasks = [\Mockery::mock(Task::class), \Mockery::mock(Task::class)];

        $this->taskRepositoryMock
            ->expects('findBy')
            ->once()
            ->with(['board' => $boardId])
            ->andReturn($mockTasks);

        $result = $this->service->fetchBoardTask($boardId);

        $this->assertSame($mockTasks, $result);
    }

    public function testCreateTaskSuccess(): void
    {
        $dto = \Mockery::mock(TaskDto::class);
        $dto->expects('getName')->andReturn('New Task');
        $dto->expects('getDescription')->andReturn('Task description');
        $dto->expects('getBoardId')->andReturn(10);

        $boardMock = \Mockery::mock(Board::class);
        $stateMock = \Mockery::mock(TaskState::class);

        $this->boardRepositoryMock
            ->expects('findOneBy')
            ->once()
            ->with(['id' => 10])
            ->andReturn($boardMock);

        $this->taskStateRepositoryMock
            ->expects('findOneBy')
            ->once()
            ->with(['order' => 1])
            ->andReturn($stateMock);

        $this->entityManagerMock->expects('beginTransaction')->once();
        $this->entityManagerMock->expects('persist')->once()->with(\Mockery::type(Task::class));
        $this->entityManagerMock->expects('flush')->once();
        $this->entityManagerMock->expects('commit')->once();
        $this->entityManagerMock->expects('rollback')->never();

        $response = $this->service->createTask($dto);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreateTaskRollsBackOnException(): void
    {
        $dto = \Mockery::mock(TaskDto::class);
        $dto->expects('getName')->andReturn('New Task');
        $dto->expects('getDescription')->andReturn('Task description');
        $dto->expects('getBoardId')->andReturn(10);

        $boardMock = \Mockery::mock(Board::class);
        $stateMock = \Mockery::mock(TaskState::class);

        $this->boardRepositoryMock->expects('findOneBy')->andReturn($boardMock);
        $this->taskStateRepositoryMock->expects('findOneBy')->andReturn($stateMock);

        $this->entityManagerMock->expects('beginTransaction')->once();
        $this->entityManagerMock->expects('persist')->once()->with(\Mockery::type(Task::class));
        $this->entityManagerMock->expects('flush')->once()->andThrow(new \Exception('DB Error during persist'));
        $this->entityManagerMock->expects('rollback')->once();
        $this->entityManagerMock->expects('commit')->never();

        $response = $this->service->createTask($dto);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertStringContainsString('DB Error during persist', $response->getContent());
    }

    public function testEditTaskSuccess(): void
    {
        $taskId = 5;
        $dto = \Mockery::mock(TaskDto::class);
        $dto->allows('getName')->andReturn('Updated Name');
        $dto->allows('getDescription')->andReturn('Updated Desc');
        $dto->allows('getStateId')->andReturn(3);

        $taskMock = \Mockery::mock(Task::class);
        $taskStateMock = \Mockery::mock(TaskState::class);
        $taskStateMock2 = \Mockery::mock(TaskState::class);

        $this->taskRepositoryMock
            ->expects('findOneBy')
            ->once()
            ->with(['id' => $taskId])
            ->andReturn($taskMock);

        $this->taskStateRepositoryMock
            ->expects('findOneBy')
            ->once()
            ->with(['id' => 3])
            ->andReturn($taskStateMock);

        $taskMock->expects('getName')->andReturn('Old Name');
        $taskMock->expects('getDescription')->andReturn('Nothing');

        $taskMock->expects('getState')->once()->andReturn($taskStateMock2);
        $taskStateMock2->expects('getId')->once()->andReturn(22);

        $taskMock->expects('setName')->with('Updated Name')->once();
        $taskMock->expects('setDescription')->with('Updated Desc')->once();
        $taskMock->expects('setState')->with($taskStateMock)->once();

        $this->entityManagerMock->expects('beginTransaction')->once();
        $this->entityManagerMock->expects('persist')->once()->with($taskMock);
        $this->entityManagerMock->expects('flush')->once();
        $this->entityManagerMock->expects('commit')->once();
        $this->entityManagerMock->expects('rollback')->never();

        $response = $this->service->editTask($taskId, $dto);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Task updated', $response->getContent());
    }

    public function testEditTaskNotFound(): void
    {
        $taskId = 99;
        $dto = \Mockery::mock(TaskDto::class);

        $this->taskRepositoryMock
            ->expects('findOneBy')
            ->once()
            ->with(['id' => $taskId])
            ->andReturn(null);

        $this->entityManagerMock->expects('beginTransaction')->never();

        $response = $this->service->editTask($taskId, $dto);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertStringContainsString('Task not found', $response->getContent());
    }

    public function testEditTaskRollsBackOnException(): void
    {
        $taskId = 5;
        $dto = \Mockery::mock(TaskDto::class);
        $dto->allows('getName')->andReturn('Updated Name');
        $dto->allows('getDescription')->andReturn('Updated Desc');
        $dto->allows('getStateId')->andReturn(3);

        $taskMock = \Mockery::mock(Task::class);
        $taskStateMock = \Mockery::mock(TaskState::class);
        $taskStateMock2 = \Mockery::mock(TaskState::class);

        $this->taskRepositoryMock->expects('findOneBy')->andReturn($taskMock);
        $this->taskStateRepositoryMock->expects('findOneBy')->andReturn($taskStateMock);

        $taskMock->expects('getName')->andReturn('Old Name');
        $taskMock->expects('getDescription')->andReturn('Nothing');

        $taskMock->expects('getState')->once()->andReturn($taskStateMock2);
        $taskStateMock2->expects('getId')->once()->andReturn(22);

        $taskMock->expects('setName')->once();
        $taskMock->expects('setDescription')->once();
        $taskMock->expects('setState')->once();

        $this->entityManagerMock->expects('beginTransaction')->once();
        $this->entityManagerMock->expects('persist')->once()->with($taskMock);
        $this->entityManagerMock->expects('flush')->once()->andThrow(new \Exception('DB Error during update'));
        $this->entityManagerMock->expects('rollback')->once();
        $this->entityManagerMock->expects('commit')->never();

        $response = $this->service->editTask($taskId, $dto);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertStringContainsString('Update failed: DB Error during update', $response->getContent());
    }

    public function testDeleteTaskSuccess(): void
    {
        $taskId = 5;
        $taskMock = \Mockery::mock(Task::class);

        $this->taskRepositoryMock
            ->expects('findOneBy')
            ->once()
            ->with(['id' => $taskId])
            ->andReturn($taskMock);

        // EntityManager retire et flush
        $this->entityManagerMock->expects('remove')->once()->with($taskMock);
        $this->entityManagerMock->expects('flush')->once();

        $response = $this->service->deleteTask($taskId);
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertStringContainsString('Task deleted', $response->getContent());
    }

    public function testDeleteTaskNotFound(): void
    {
        $taskId = 99;
        $this->taskRepositoryMock
            ->expects('findOneBy')
            ->once()
            ->with(['id' => $taskId])
            ->andReturn(null);

        $response = $this->service->deleteTask($taskId);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertStringContainsString('Task not found', $response->getContent());
    }

    public function testDeleteTaskFailure(): void
    {
        $taskId = 5;
        $taskMock = \Mockery::mock(Task::class);

        $this->taskRepositoryMock->expects('findOneBy')->andReturn($taskMock);

        $this->entityManagerMock->expects('remove')->once()->with($taskMock);
        $this->entityManagerMock->expects('flush')->once()->andThrow(new \Exception('DB Error during delete'));

        $response = $this->service->deleteTask($taskId);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertStringContainsString('Delete task failed DB Error during delete', $response->getContent());
    }
}
