<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Board;
use App\Repository\BoardRepository;

class BoardService
{
    private BoardRepository $boardRepository;

    public function __construct(BoardRepository $boardRepository)
    {
        $this->boardRepository = $boardRepository;
    }

    public function fetchAllUserBoard(): array
    {
        return $this->boardRepository->findAll();
    }

    public function fetchBoardById(int $id): Board
    {
        return $this->boardRepository->findOneBy(['id' => $id]);
    }
}
