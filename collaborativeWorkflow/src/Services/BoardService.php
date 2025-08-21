<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\BoardDto;
use App\Entity\Board;
use App\Repository\BoardRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class BoardService
{
    private BoardRepository $boardRepository;
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private UserRoleService $userRoleService;

    public function __construct(BoardRepository $boardRepository, UserRepository $userRepository, EntityManagerInterface $entityManager, UserRoleService $userRoleService)
    {
        $this->boardRepository = $boardRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->userRoleService = $userRoleService;
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

            // until the auth flow is implemented we mock the currentUser
            $currentUser = $this->userRepository->findOneBy(['id' => 1]);
            $this->userRoleService->linkBoardToUser($board, $currentUser);

            return new JsonResponse(['status' => 'Board created'], 201);
        } catch (\Throwable $e) {
            $this->entityManager->rollback();

            return new JsonResponse(['status' => 'Creation failed: '.$e->getMessage()], 500);
        }
    }
}
