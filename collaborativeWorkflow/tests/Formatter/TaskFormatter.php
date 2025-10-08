<?php

declare(strict_types=1);

namespace App\Tests\Formatter;

use App\Entity\Task;
use App\Entity\TaskState;
use App\Formatter\TaskFormatter;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class TaskFormatterTest extends MockeryTestCase
{
    public function testFormatTasks(): void
    {
        $stateMock1 = \Mockery::mock(TaskState::class);
        $stateMock1->shouldReceive('getId')->andReturn(1);
        $stateMock1->shouldReceive('getName')->andReturn('To do');
        $stateMock1->shouldReceive('getOrder')->andReturn(1);

        $stateMock2 = \Mockery::mock(TaskState::class);
        $stateMock2->shouldReceive('getId')->andReturn(3);
        $stateMock2->shouldReceive('getName')->andReturn('Finished');
        $stateMock2->shouldReceive('getOrder')->andReturn(3);

        $taskMock1 = \Mockery::mock(Task::class);
        $taskMock1->shouldReceive('getName')->andReturn('Task Alpha');
        $taskMock1->shouldReceive('getDescription')->andReturn('Initial setup');
        $taskMock1->shouldReceive('getState')->andReturn($stateMock1);

        $taskMock2 = \Mockery::mock(Task::class);
        $taskMock2->shouldReceive('getName')->andReturn('Task Beta');
        $taskMock2->shouldReceive('getDescription')->andReturn('Final review');
        $taskMock2->shouldReceive('getState')->andReturn($stateMock2);

        $boardTasks = [$taskMock1, $taskMock2];

        $formatter = new TaskFormatter();
        $result = $formatter->formatTasks($boardTasks);

        $expected = [
            [
                'name' => 'Task Alpha',
                'description' => 'Initial setup',
                'state' => [
                    'id' => 1,
                    'name' => 'To do',
                    'order' => 1,
                ],
            ],
            [
                'name' => 'Task Beta',
                'description' => 'Final review',
                'state' => [
                    'id' => 3,
                    'name' => 'Finished',
                    'order' => 3,
                ],
            ],
        ];

        $this->assertEquals($expected, $result);
    }
}
