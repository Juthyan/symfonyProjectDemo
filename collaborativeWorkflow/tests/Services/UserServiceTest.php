<?php

declare(strict_types=1);

namespace App\Tests\Services;

use App\DTO\UserDto;
use App\Entity\User;
use App\Entity\UserRole;
use App\Repository\UserRepository;
use App\Repository\UserRoleRepository;
use App\Services\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserServiceTest extends MockeryTestCase
{
    private $userRepositoryMock;
    private $entityManagerMock;
    private $userRoleRepository;
    private $passwordHasherInterfaceMock;

    private UserService $userService;

    protected function setUp(): void
    {
        $this->userRepositoryMock = m::mock(UserRepository::class);
        $this->entityManagerMock = m::mock(EntityManagerInterface::class);
        $this->userRoleRepository = m::mock(UserRoleRepository::class);
        $this->passwordHasherInterfaceMock = m::mock(UserPasswordHasherInterface::class);
        $this->userService = new UserService($this->userRepositoryMock, $this->entityManagerMock, $this->userRoleRepository, $this->passwordHasherInterfaceMock);
    }

    public function testFetchUsersReturnsArray()
    {
        $users = [
            m::mock(User::class),
            m::mock(User::class),
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
        $userMock = m::mock(User::class);

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
        $userDto = new UserDto('testuser', 'test@example.com', 'test');
        $this->passwordHasherInterfaceMock->expects('hashPassword')->once()->andReturn('test');

        // Expect entity manager methods to be called in this order
        $this->entityManagerMock->expects('beginTransaction')->once();
        $this->entityManagerMock->expects('persist')->once()->with(m::on(function ($user) use ($userDto) {
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
        $userDto = new UserDto('failuser', 'fail@example.com', 'test');
        $this->passwordHasherInterfaceMock->expects('hashPassword')->once()->andReturn('test');

        $this->entityManagerMock->expects('beginTransaction')->once();
        $this->entityManagerMock->expects('persist')->once()->andThrow(new \Exception('DB error'));
        $this->entityManagerMock->expects('rollback')->once();

        $response = $this->userService->createUser($userDto);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertStringContainsString('Creation failed', $response->getContent());
        $this->assertStringContainsString('DB error', $response->getContent());
    }

    public function testEditUserSuccessWithUserRoles()
    {
        $dto = m::mock(UserDto::class);
        $dto->userRoleIds = [1, 2];
        $dto->expects('getUserName')->once()->andReturn('john_doe');
        $dto->expects('getMail')->once()->andReturn('john@example.com');
        $dto->expects('getPassword')->once()->andReturn(null);

        $user = m::mock(User::class);

        $this->userRepositoryMock
            ->expects('findOneBy')
            ->once()
            ->with(['id' => 3])
            ->andReturn($user);

        $user->expects('setUserName')->once()->with('john_doe');
        $user->expects('setMail')->once()->with('john@example.com');

        $userRole1 = m::mock(UserRole::class);
        $userRole2 = m::mock(UserRole::class);

        $this->userRoleRepository
            ->expects('findBy')
            ->once()
            ->with(['id' => [1, 2]])
            ->andReturn([$userRole1, $userRole2]);

        $user->expects('setUserRoles')->once()->with(m::type(\Doctrine\Common\Collections\ArrayCollection::class));

        $this->entityManagerMock->expects('beginTransaction')->once();
        $this->entityManagerMock->expects('persist')->once()->with(m::type(User::class));
        $this->entityManagerMock->expects('flush')->once();
        $this->entityManagerMock->expects('commit')->once();
        // No need to mock saveUser, assuming it works

        $response = $this->userService->editUser(3, $dto);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('User updated', $data['status']);
    }

    public function testEditUserFailure()
    {
        $dto = m::mock(UserDto::class);
        $dto->userRoleIds = [1, 2];
        $dto->expects('getUserName')->once()->andReturn('john_doe');
        $dto->expects('getMail')->once()->andReturn('john@example.com');
        $dto->expects('getPassword')->twice()->andReturn('test');

        $user = m::mock(User::class);

        $this->userRepositoryMock
            ->expects('findOneBy')
            ->once()
            ->with(['id' => 3])
            ->andReturn($user);

        $this->passwordHasherInterfaceMock->expects('hashPassword')->once()->andReturn('test');
        $user->expects('setUserName')->once()->with('john_doe');
        $user->expects('setMail')->once()->with('john@example.com');
        $user->expects('setPassword')->once()->with('test');

        $userRole1 = m::mock(UserRole::class);
        $userRole2 = m::mock(UserRole::class);

        $this->userRoleRepository
            ->expects('findBy')
            ->once()
            ->with(['id' => [1, 2]])
            ->andReturn([$userRole1, $userRole2]);

        $user->expects('setUserRoles')->once()->with(m::type(\Doctrine\Common\Collections\ArrayCollection::class));

        $this->entityManagerMock->expects('beginTransaction')->once();
        $this->entityManagerMock->expects('persist')->once()->with(m::type(User::class));
        // Here, simulate an exception when flush is called
        $this->entityManagerMock->expects('flush')->once()->andThrow(new \Exception('Save failed'));
        $this->entityManagerMock->expects('rollback')->once();
        $this->entityManagerMock->expects('commit')->never();

        $response = $this->userService->editUser(3, $dto);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Update failed: Save failed', $data['status']);
    }

    public function testEditUserNotFound()
    {
        $dto = m::mock(UserDto::class);

        $this->userRepositoryMock
            ->expects('findOneBy')
            ->once()
            ->with(['id' => 999])
            ->andReturn(null);

        $response = $this->userService->editUser(999, $dto);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('User not found', $data['status']);
    }

    public function testDeleteUserSuccess()
    {
        $user = m::mock(User::class);

        $this->userRepositoryMock
            ->expects('findOneBy')
            ->once()
            ->with(['id' => 7])
            ->andReturn($user);

        $this->entityManagerMock
            ->expects('remove')
            ->once()
            ->with($user);

        $this->entityManagerMock
            ->expects('flush')
            ->once();

        $response = $this->userService->deleteUser(7);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(204, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('User deleted', $data['status']);
    }

    public function testDeleteUserNotFound()
    {
        $this->userRepositoryMock
            ->expects('findOneBy')
            ->once()
            ->with(['id' => 888])
            ->andReturn(null);

        $response = $this->userService->deleteUser(888);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('User not found', $data['status']);
    }

    public function testDeleteUserFails()
    {
        $user = m::mock(User::class);

        $this->userRepositoryMock
            ->expects('findOneBy')
            ->once()
            ->with(['id' => 9])
            ->andReturn($user);

        $this->entityManagerMock
            ->expects('remove')
            ->once()
            ->with($user)
            ->andThrow(new \Exception('Delete error'));

        $response = $this->userService->deleteUser(9);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Delete user failed', $data['status']);
    }
}
