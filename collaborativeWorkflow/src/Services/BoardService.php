<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\BoardDto;
use App\Entity\Board;
use App\Repository\BoardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class BoardService
{
    private BoardRepository $boardRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(BoardRepository $boardRepository, EntityManagerInterface $entityManager)
    {
        $this->boardRepository = $boardRepository;
        $this->entityManager = $entityManager;
    }

    public function fetchAllUserBoard(): array
    {
        return $this->boardRepository->findAll();
    }

    public function fetchBoardById(int $id): Board
    {
        return $this->boardRepository->findOneBy(['id' => $id]);
    }

    public function createBoard(BoardDto $dto): JsonResponse
    {
        $board = new Board();
        $board->setName($dto->name);

        try {
            $this->entityManager->beginTransaction();

            $this->entityManager->persist($board);
            $this->entityManager->flush();
            $this->entityManager->commit();

            return new JsonResponse(['status' => 'Board created'], 201);
        } catch (\Throwable $e) {
            $this->entityManager->rollback();

            return new JsonResponse(['status' => 'Creation failed: '.$e->getMessage()], 500);
        }
    }
}
