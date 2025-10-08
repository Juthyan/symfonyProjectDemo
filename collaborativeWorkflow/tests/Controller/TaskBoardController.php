<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\TaskBoardController;
use App\DTO\TaskDto;
use App\Entity\Task;
use App\Formatter\TaskFormatter;
use App\Services\TaskService;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TaskBoardControllerTest extends MockeryTestCase
{
    private $taskServiceMock;
    private $taskFormatterMock;
    private TaskBoardController $controller;

    protected function setUp(): void
    {
        $this->taskServiceMock = \Mockery::mock(TaskService::class);
        $this->taskFormatterMock = \Mockery::mock(TaskFormatter::class);

        $this->controller = new TaskBoardController(
            $this->taskServiceMock,
            $this->taskFormatterMock
        );
    }

    public function testFetchBoardTasksReturnsFormattedTasks(): void
    {
        $boardId = 42;
        $rawTasks = [\Mockery::mock(Task::class), \Mockery::mock(Task::class)];
        $formattedData = [
            ['name' => 'Task 1', 'state' => ['name' => 'To Do']],
            ['name' => 'Task 2', 'state' => ['name' => 'Done']],
        ];

        $this->taskServiceMock
            ->expects('fetchBoardTask')
            ->once()
            ->with($boardId)
            ->andReturn($rawTasks);

        $this->taskFormatterMock
            ->expects('formatTasks')
            ->once()
            ->with($rawTasks)
            ->andReturn($formattedData);

        $response = $this->controller->fetchBoardTasks($boardId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals(json_encode($formattedData), $response->getContent());
    }

    public function testCreateTaskCallsServiceAndReturnsResponse(): void
    {
        $dtoMock = \Mockery::mock(TaskDto::class);
        $expectedResponse = new JsonResponse(['status' => 'Task created'], Response::HTTP_OK);

        $this->taskServiceMock
            ->expects('createTask')
            ->once()
            ->with($dtoMock)
            ->andReturn($expectedResponse);

        $response = $this->controller->createTask($dtoMock);
        $this->assertSame($expectedResponse, $response);
    }

    public function testEditTaskCallsServiceAndReturnsResponse(): void
    {
        $taskId = 15;
        $dtoMock = \Mockery::mock(TaskDto::class);
        $expectedResponse = new JsonResponse(['status' => 'Task updated'], Response::HTTP_OK);

        $this->taskServiceMock
            ->expects('editTask')
            ->once()
            ->with($taskId, $dtoMock)
            ->andReturn($expectedResponse);

        $response = $this->controller->editTask($taskId, $dtoMock);
        $this->assertSame($expectedResponse, $response);
    }

    public function testDeleteTaskCallsServiceAndReturnsResponse(): void
    {
        $taskId = 20;
        $expectedResponse = new JsonResponse(['status' => 'Task deleted'], Response::HTTP_NO_CONTENT);

        $this->taskServiceMock
            ->expects('deleteTask')
            ->once()
            ->with($taskId)
            ->andReturn($expectedResponse);

        $response = $this->controller->deleteTask($taskId);
        $this->assertSame($expectedResponse, $response);
    }
}
