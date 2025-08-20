<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\BoardController;
use App\Entity\Board;
use App\Formatter\BoardFormatter;
use App\Services\BoardService;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

final class BoardControllerTest extends MockeryTestCase
{
    public function testFetchAllUserBoard(): void
    {
        $boardMock = \Mockery::mock(Board::class);
        $formattedBoard = ['id' => 1, 'name' => 'Test Board', 'role' => []];

        $boardServiceMock = \Mockery::mock(BoardService::class);
        $boardServiceMock->expects('fetchAllUserBoard')
            ->once()
            ->andReturn([$boardMock]);

        $boardFormatterMock = \Mockery::mock(BoardFormatter::class);
        $boardFormatterMock->expects('formatBoard')
            ->once()
            ->with($boardMock)
            ->andReturn($formattedBoard);

        $controller = new BoardController($boardServiceMock, $boardFormatterMock);

        $response = $controller->fetchAllUserBoard();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(json_encode([$formattedBoard]), $response->getContent());
    }

    public function testFetchBoard(): void
    {
        $boardMock = \Mockery::mock(Board::class);
        $formattedBoard = ['id' => 1, 'name' => 'Test Board', 'role' => []];

        $boardServiceMock = \Mockery::mock(BoardService::class);
        $boardServiceMock->expects('fetchBoardById')
            ->once()
            ->with(1)
            ->andReturn($boardMock);

        $boardFormatterMock = \Mockery::mock(BoardFormatter::class);
        $boardFormatterMock->expects('formatBoard')
            ->once()
            ->with($boardMock)
            ->andReturn($formattedBoard);

        $controller = new BoardController($boardServiceMock, $boardFormatterMock);

        $response = $controller->fetchBoard(1);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(json_encode($formattedBoard), $response->getContent());
    }
}
