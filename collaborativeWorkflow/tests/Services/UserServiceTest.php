<?php

declare(strict_types=1);

namespace App\Tests\Services;

use App\DTO\UserDto;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Services\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserServiceTest extends MockeryTestCase
{
    private $userRepositoryMock;
    private $entityManagerMock;
    private UserService $userService;

    protected function setUp(): void
    {
        $this->userRepositoryMock = \Mockery::mock(UserRepository::class);
        $this->entityManagerMock = \Mockery::mock(EntityManagerInterface::class);
        $this->userService = new UserService($this->userRepositoryMock, $this->entityManagerMock);
    }

    public function testFetchUsersReturnsArray()
    {
        $users = [
            \Mockery::mock(User::class),
            \Mockery::mock(User::class),
        ];

        $this->userRepositoryMock
            ->expects('findAll')
            ->once()
            ->andReturn($users);

        $result = $this->userService->fetchUsers();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    public function testFetchUserByUsernameReturnsUser()
    {
        $userMock = \Mockery::mock(User::class);

        $this->userRepositoryMock
            ->expects('findOneBy')
            ->with(['userName' => 'johndoe'])
            ->once()
            ->andReturn($userMock);

        $result = $this->userService->fetchUserByUsername('johndoe');

        $this->assertSame($userMock, $result);
    }

    public function testCreateUserSuccess()
    {
        $userDto = new UserDto('testuser', 'test@example.com');

        // Expect entity manager methods to be called in this order
        $this->entityManagerMock->expects('beginTransaction')->once();
        $this->entityManagerMock->expects('persist')->once()->with(\Mockery::on(function ($user) use ($userDto) {
            return $user instanceof User
                && $user->getUsername() === $userDto->getUserName()
                && $user->getEmail() === $userDto->getMail();
        }));
        $this->entityManagerMock->expects('flush')->once();
        $this->entityManagerMock->expects('commit')->once();

        $response = $this->userService->createUser($userDto);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertStringContainsString('User created', $response->getContent());
    }

    public function testCreateUserFailure()
    {
        $userDto = new UserDto('failuser', 'fail@example.com');

        $this->entityManagerMock->expects('beginTransaction')->once();
        $this->entityManagerMock->expects('persist')->once()->andThrow(new \Exception('DB error'));
        $this->entityManagerMock->expects('rollback')->once();

        $response = $this->userService->createUser($userDto);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertStringContainsString('Creation failed', $response->getContent());
        $this->assertStringContainsString('DB error', $response->getContent());
    }
}
