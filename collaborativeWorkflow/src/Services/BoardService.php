<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\BoardDto;
use App\Entity\Board;
use App\Repository\BoardRepository;
use App\Repository\UserRepository;
use App\Repository\UserRoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class BoardService
{
    private BoardRepository $boardRepository;
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private UserRoleService $userRoleService;
    private UserRoleRepository $userRoleRepository;

    public function __construct(BoardRepository $boardRepository, UserRepository $userRepository, EntityManagerInterface $entityManager, UserRoleService $userRoleService, UserRoleRepository $userRoleRepository)
    {
        $this->boardRepository = $boardRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->userRoleService = $userRoleService;
        $this->userRoleRepository = $userRoleRepository;
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
            $this->saveBoard($board);

            // until the auth flow is implemented we mock the currentUser
            $currentUser = $this->userRepository->findOneBy(['id' => 1]);
            $this->userRoleService->linkBoardToUser($board, $currentUser);

            return new JsonResponse(['status' => 'Board created'], 201);
        } catch (\Throwable $e) {
            return new JsonResponse(['status' => 'Creation failed: '.$e->getMessage()], 500);
        }
    }

    public function editBoard(int $id, BoardDto $dto): JsonResponse
    {
        $board = $this->boardRepository->findOneBy(['id' => $id]);

        if (!$board) {
            return new JsonResponse(['status' => 'Board not found'], 404);
        }

        $board->setName($dto->name);

        if (!empty($dto->userRoleIds)) {
            $userRoleIds = $dto->userRoleIds;
            $userRoles = $this->userRoleRepository->findBy(['id' => $userRoleIds]);
            $board->setUserRoles(new ArrayCollection($userRoles));
        }

        try {
            $this->saveBoard($board);

            return new JsonResponse(['status' => 'Board edited'], 200);
        } catch (\Throwable $e) {
            return new JsonResponse(['status' => 'Edit failed: '.$e->getMessage()], 500);
        }
    }

    public function deleteBoard(int $id)
    {
        $board = $this->boardRepository->findOneBy(['id' => $id]);
        try {
            $this->entityManager->remove($board);
            $this->entityManager->flush();

            return new JsonResponse(['status' => 'Board deleted'], 204);
        } catch (\Throwable $e) {
            return new JsonResponse(['status' => 'Delete failed: '.$e->getMessage()], 500);
        }
    }

    private function saveBoard(Board $board): void
    {
        try {
            $this->entityManager->beginTransaction();
            $this->entityManager->persist($board);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }
}
