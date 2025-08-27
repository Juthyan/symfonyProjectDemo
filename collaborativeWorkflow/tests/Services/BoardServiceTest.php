<?php

declare(strict_types=1);

namespace App\Tests\Services;

use App\DTO\BoardDto;
use App\Entity\Board;
use App\Entity\User;
use App\Repository\BoardRepository;
use App\Repository\UserRepository;
use App\Repository\UserRoleRepository;
use App\Services\BoardService;
use App\Services\UserRoleService;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Common\Collections\ArrayCollection;


class BoardServiceTest extends MockeryTestCase
{
    private $boardRepository;
    private $userRepository;
    private $entityManager;
    private $userRoleService;
    private $userRoleRepository;
    private BoardService $boardService;

    protected function setUp(): void
    {
        $this->boardRepository = m::mock(BoardRepository::class);
        $this->userRepository = m::mock(UserRepository::class);
        $this->entityManager = m::mock(EntityManagerInterface::class);
        $this->userRoleService = m::mock(UserRoleService::class);
        $this->userRoleRepository = m::mock(UserRoleRepository::class);

        $this->boardService = new BoardService(
            $this->boardRepository,
            $this->userRepository,
            $this->entityManager,
            $this->userRoleService,
            $this->userRoleRepository
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

    public function testEditBoardSuccessWithUserRoles()
    {
        $dto = new BoardDto('Updated Name', [1, 2]);
        $board = m::mock(Board::class);
        $userRole1 = m::mock();
        $userRole2 = m::mock();

        $this->boardRepository
            ->expects('findOneBy')
            ->once()
            ->with(['id' => 123])
            ->andReturn($board);

        $board->shouldReceive('setName')->once()->with('Updated Name');
        $this->userRoleRepository
            ->expects('findBy')
            ->once()
            ->with(['id' => [1, 2]])
            ->andReturn([$userRole1, $userRole2]);

        $board->shouldReceive('setUserRoles')->once()->with(m::type(ArrayCollection::class));

        $this->entityManager->expects('beginTransaction')->once();
        $this->entityManager->expects('persist')->once()->with($board);
        $this->entityManager->expects('flush')->once();
        $this->entityManager->expects('commit')->once();

        $response = $this->boardService->editBoard(123, $dto);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Board edited', $data['status']);
    }

    public function testEditBoardNotFound()
    {
        $dto = new BoardDto('Updated Name');

        $this->boardRepository
            ->expects('findOneBy')
            ->once()
            ->with(['id' => 999])
            ->andReturn(null);

        $response = $this->boardService->editBoard(999, $dto);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Board not found', $data['status']);
    }

    public function testEditBoardThrowsException()
    {
        $dto = new BoardDto('Updated Name');
        $board = m::mock(Board::class);

        $this->boardRepository
            ->shouldReceive('findOneBy')
            ->once()
            ->with(['id' => 456])
            ->andReturn($board);

        $board->shouldReceive('setName')->once()->with('Updated Name');

        $this->entityManager->expects('beginTransaction')->once();
        $this->entityManager->expects('persist')->once()->with($board);
        $this->entityManager->expects('flush')->once()->andThrow(new \Exception('Save failed'));
        $this->entityManager->expects('rollback')->once();

        $response = $this->boardService->editBoard(456, $dto);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Edit failed', $data['status']);
    }

    public function testDeleteBoardSuccess()
    {
        $board = m::mock(Board::class);

        $this->boardRepository
            ->expects('findOneBy')
            ->once()
            ->with(['id' => 1])
            ->andReturn($board);

        $this->entityManager
            ->expects('remove')
            ->once()
            ->with($board);

        $response = $this->boardService->deleteBoard(1);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(204, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Board deleted', $data['status']);
    }
    public function testDeleteBoardFailure()
    {
        $board = m::mock(Board::class);

        $this->boardRepository
            ->expects('findOneBy')
            ->once()
            ->with(['id' => 2])
            ->andReturn($board);

        $this->entityManager
            ->expects('remove')
            ->once()
            ->with($board)
            ->andThrow(new \Exception('Delete failed'));

        $response = $this->boardService->deleteBoard(2);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Delete failed', $data['status']);
    }
}
