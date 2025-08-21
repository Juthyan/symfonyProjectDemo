<?php

declare(strict_types=1);

namespace App\Tests\Services;

use App\DTO\BoardDto;
use App\Entity\Board;
use App\Entity\User;
use App\Repository\BoardRepository;
use App\Repository\UserRepository;
use App\Services\BoardService;
use App\Services\UserRoleService;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Symfony\Component\HttpFoundation\JsonResponse;

class BoardServiceTest extends MockeryTestCase
{
    private $boardRepository;
    private $userRepository;
    private $entityManager;
    private $userRoleService;
    private BoardService $boardService;

    protected function setUp(): void
    {
        $this->boardRepository = m::mock(BoardRepository::class);
        $this->userRepository = m::mock(UserRepository::class);
        $this->entityManager = m::mock(EntityManagerInterface::class);
        $this->userRoleService = m::mock(UserRoleService::class);

        $this->boardService = new BoardService(
            $this->boardRepository,
            $this->userRepository,
            $this->entityManager,
            $this->userRoleService
        );
    }

    public function testCreateBoardSuccess()
    {
        $dto = new BoardDto('Test Board');

        $user = m::mock(User::class);

        // Expect beginTransaction, persist, flush, commit to be called once
        $this->entityManager->expects('beginTransaction')->once();
        $this->entityManager->expects('persist')->once()->with(m::type(Board::class));
        $this->entityManager->expects('flush')->once();
        $this->entityManager->expects('commit')->once();

        // Expect rollback never called on success
        $this->entityManager->expects('rollback')->never();

        // Expect userRepository->findOneBy(['id' => 1]) called and returns user mock
        $this->userRepository->expects('findOneBy')->once()->with(['id' => 1])->andReturn($user);

        // Expect userRoleService->linkBoardToUser called once with Board and User
        $this->userRoleService->expects('linkBoardToUser')->once()->with(
            m::type(Board::class),
            $user
        );

        $response = $this->boardService->createBoard($dto);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Board created', $data['status']);
    }

    public function testCreateBoardFailureRollsBack()
    {
        $dto = new BoardDto('Test Board');

        $this->entityManager->expects('beginTransaction')->once();
        $this->entityManager->expects('persist')->once()->with(m::type(Board::class));

        // Simulate exception during flush
        $this->entityManager->expects('commit')->never();
        $this->entityManager->expects('rollback')->once();

        // Throw an exception when flush is called
        $this->entityManager->expects('flush')->andThrow(new \Exception('DB error'));

        // userRepository and userRoleService should not be called due to exception
        $this->userRepository->expects('findOneBy')->never();
        $this->userRoleService->expects('linkBoardToUser')->never();

        $response = $this->boardService->createBoard($dto);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Creation failed', $data['status']);
    }
}
