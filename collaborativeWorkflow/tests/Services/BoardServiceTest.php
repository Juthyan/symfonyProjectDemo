<?php

declare(strict_types=1);

namespace App\Tests\Services;

use App\Entity\Board;
use App\Repository\BoardRepository;
use App\Services\BoardService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class BoardServiceTest extends MockeryTestCase
{
    private $boardRepositoryMock;
    private BoardService $boardService;

    protected function setUp(): void
    {
        $this->boardRepositoryMock = Mockery::mock(BoardRepository::class);
        $this->boardService = new BoardService($this->boardRepositoryMock);
    }

    public function testFetchAllUserBoardReturnsArray()
    {
        $boards = [
            Mockery::mock(Board::class),
            Mockery::mock(Board::class),
        ];

        $this->boardRepositoryMock
            ->expects('findAll')
            ->once()
            ->andReturn($boards);

        $result = $this->boardService->fetchAllUserBoard();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    public function testFetchBoardByIdReturnsBoard()
    {
        $boardMock = Mockery::mock(Board::class);

        $this->boardRepositoryMock
            ->expects('findOneBy')
            ->with(['id' => 123])
            ->once()
            ->andReturn($boardMock);

        $result = $this->boardService->fetchBoardById(123);

        $this->assertSame($boardMock, $result);
    }
}
