<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\BoardDto;
use App\Formatter\BoardFormatter;
use App\Services\BoardService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/boards')]
final class BoardController extends AbstractController
{
    private BoardService $boardService;
    private BoardFormatter $boardFormatter;

    public function __construct(BoardService $boardService, BoardFormatter $boardFormatter)
    {
        $this->boardService = $boardService;
        $this->boardFormatter = $boardFormatter;
    }

    #[Route('', name: 'user_boards')]
    public function fetchAllUserBoard(): Response
    {
        $boards = $this->boardService->fetchAllUserBoard();
        $data = [];
        foreach ($boards as $board) {
            $data[] = $this->boardFormatter->formatBoard($board);
        }

        return new JsonResponse($data);
    }

    #[Route('/{id}', name: 'get_board', methods: ['GET'])]
    public function fetchBoard(int $id): Response
    {
        $board = $this->boardService->fetchBoardById($id);

        return new JsonResponse($this->boardFormatter->formatBoard($board));
    }

    #[Route('/save', name: 'create_board', methods: ['POST'])]
    public function createBoard(#[MapRequestPayload] BoardDto $boardDto): Response
    {
        return $this->boardService->createBoard($boardDto);
    }

    #[Route('/edit/{id}', name: 'edit_board', methods: ['PUT'])]
    public function editBoard(int $id, #[MapRequestPayload] BoardDto $dto): Response
    {
        return $this->boardService->editBoard($id, $dto);
    }

    #[Route('/{id}', name: 'delete_board', methods: ['DELETE'])]
    public function deleteBoard(int $id): Response
    {
        return $this->boardService->deleteBoard($id);
    }
}
